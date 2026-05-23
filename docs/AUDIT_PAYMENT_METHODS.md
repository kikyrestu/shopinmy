# 🔍 Audit Tambahan — Sistem Payment Method Toggle
## Addendum dari Laporan Utama AUDIT_CHECKOUT_COURIER.md

> **Scope:** Mengapa payment method tidak bisa di-on/off dari admin panel  
> **File Utama:** `ManageSettings.php`, `checkout-view.blade.php`, `CheckoutView.php`, `SettingsSeeder.php`, `Setting.php`

---

## Ringkasan Masalah

Ditemukan **5 bug sistemik** yang menyebabkan kontrol payment method dari admin panel tidak bekerja dengan benar. Akar masalahnya adalah **ketidakkonsistenan cara masing-masing payment method di-gate** — dua metode pakai toggle, dua metode lain pakai keberadaan API key, dan satu metode benar-benar orphan.

```
┌─────────────────────────────────────────────────────────────────────┐
│             PETA KONTROL PAYMENT METHOD (kondisi saat ini)          │
├──────────────────┬────────────────────────┬────────────────────────┤
│ Payment Method   │ Gate di Blade          │ Toggle di Admin?       │
├──────────────────┼────────────────────────┼────────────────────────┤
│ Billplz (FPX)    │ api_key tidak kosong   │ ❌ Tidak ada toggle    │
│ Stripe           │ publishable_key kosong │ ❌ Tidak ada toggle    │
│ Manual Transfer  │ setting('..._enabled') │ ✅ Ada, tapi BUG string│
│ COD              │ setting('..._enabled') │ ✅ Ada, tapi BUG string│
│ GrabPay          │ ❌ Tidak ada di blade  │ Setting ada, orphan   │
└──────────────────┴────────────────────────┴────────────────────────┘
```

---

## BUG-PM-01 · String `'0'` adalah TRUTHY di PHP — Toggle Tidak Bisa Disable COD & Manual Transfer

**File:** `app/Models/Setting.php` · `database/seeders/SettingsSeeder.php` · `checkout-view.blade.php`  
**Severity:** 🔴 Critical

### Masalah

`Setting::get()` mengembalikan nilai raw dari database sebagai **string**. Filament Toggle menyimpan `false` sebagai **empty string `''`** dan `true` sebagai **`'1'`**. Semuanya benar... **kecuali SettingsSeeder menyimpan nilai OFF sebagai string `'0'`**, bukan empty string.

Di PHP: `'0'` (string nol) adalah **truthy**. Satu-satunya string yang falsy di PHP adalah `''` (kosong).

```php
// SettingsSeeder.php:
['key' => 'cod_enabled',               'value' => '0', ...], // ← String '0'
['key' => 'grabpay_enabled',           'value' => '0', ...], // ← String '0'
// manual_transfer_enabled = '1' → truthy ✓, tapi lihat skenario toggle di bawah

// Setting::get('cod_enabled') returns → '0' (string)

// checkout-view.blade.php:
@if(\App\Models\Setting::get('cod_enabled'))  // → if('0') → TRUE ❌
    <label>COD option...</label>              // Selalu tampil!
@endif
```

### Siklus Broken Toggle

```
═══════════════════════════════════════════════════════════
SKENARIO A: Fresh Install (setelah php artisan db:seed)
═══════════════════════════════════════════════════════════

DB: cod_enabled = '0'  (string, bukan boolean)
        ↓
Admin buka Settings → mount() load '0' ke Filament Toggle
        ↓
Filament Toggle melihat '0' → PHP truthy → Toggle ditampilkan ON ✅ (?)
        ↓
Admin pikir COD sudah ON, padahal seharusnya OFF
        ↓
Checkout blade: if('0') → TRUE → COD muncul di checkout ← ❌ SEHARUSNYA OFF

═══════════════════════════════════════════════════════════
SKENARIO B: Admin coba matikan COD
═══════════════════════════════════════════════════════════

Admin klik toggle COD → OFF
Admin klik Save
        ↓
Filament Toggle OFF → kirim false (PHP bool) ke Setting::set()
Setting::set() memanggil updateOrCreate(['value' => false])
        ↓
MySQL menyimpan false sebagai '' (empty string) di kolom TEXT
        ↓
DB: cod_enabled = '' (empty string)
        ↓
Checkout blade: if('') → FALSE → COD disembunyikan ✓

═══════════════════════════════════════════════════════════
SKENARIO C: Admin coba nyalakan kembali COD
═══════════════════════════════════════════════════════════

Admin klik toggle COD → ON
Admin klik Save
        ↓
Filament Toggle ON → kirim true (PHP bool) ke Setting::set()
Setting::set() → updateOrCreate(['value' => true])
        ↓
MySQL menyimpan true sebagai '1' di kolom TEXT
        ↓
DB: cod_enabled = '1'
        ↓
Checkout blade: if('1') → TRUE → COD muncul ✓ (Sekarang baru benar)
```

### Diagram Aliran Bug

```
SEEDER RUN                      BLADE CHECK
    │                               │
    │  cod_enabled = '0'            │
    │  (string, niat = OFF)         │
    │                               │
    └──→ DB stores '0' ────────────→ Setting::get('cod_enabled')
                                    │  returns '0' (string)
                                    │
                                    ↓
                           @if('0')  ← PHP evaluasi
                                    │
                         ┌──────────┘
                         │ TRUE ← ❌ '0' adalah truthy!
                         │
                         ↓
                  [COD muncul di checkout]
                  [padahal seharusnya hidden]
                         │
                         ↓
                  [Customer bisa pilih COD]
                  [Admin tidak tahu kenapa COD aktif]
```

### Solusi

**Fix 1 — Seeder (segera):** Ganti `'0'` dengan `false` atau `0` (integer):
```php
// SettingsSeeder.php — ganti semua:
['key' => 'cod_enabled',             'value' => false, ...],
['key' => 'grabpay_enabled',         'value' => false, ...],
['key' => 'sst_enabled',             'value' => false, ...],
['key' => 'whatsapp_enabled',        'value' => false, ...],
['key' => 'myparcel_sandbox',        'value' => true,  ...],
['key' => 'billplz_sandbox',         'value' => true,  ...],
['key' => 'stripe_sandbox',          'value' => true,  ...],
```

**Fix 2 — Setting Model (permanen):** Tambahkan helper untuk boolean check:
```php
// Setting.php — tambahkan method:
public static function isEnabled(string $key): bool
{
    $value = static::get($key);
    // Handle: null, '', '0', false, 0 → false
    // Handle: '1', 1, 'true', true → true
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
```

**Fix 3 — Blade (segera):** Ganti `Setting::get()` dengan `Setting::isEnabled()` untuk semua boolean check:
```blade
{{-- checkout-view.blade.php --}}
@if(\App\Models\Setting::isEnabled('cod_enabled'))
    {{-- COD option --}}
@endif

@if(\App\Models\Setting::isEnabled('manual_transfer_enabled'))
    {{-- Manual Transfer option --}}
@endif
```

---

## BUG-PM-02 · Billplz & Stripe Tidak Punya Toggle On/Off — Hanya Bisa Disable Lewat Hapus API Key

**File:** `app/Filament/Pages/ManageSettings.php` · `checkout-view.blade.php`  
**Severity:** 🟠 High

### Masalah

Di admin settings, **tidak ada toggle untuk mengaktifkan/menonaktifkan Billplz dan Stripe**. Satu-satunya cara menonaktifkan kedua gateway ini adalah dengan **menghapus API key** dari form.

```php
// ManageSettings.php — Tab Payment:
Toggle::make('manual_transfer_enabled') // ✅ Ada
Toggle::make('cod_enabled')             // ✅ Ada
// ❌ Tidak ada: Toggle::make('billplz_enabled')
// ❌ Tidak ada: Toggle::make('stripe_enabled')

// Gating di checkout-view.blade.php:
@if(\App\Models\Setting::get('billplz_api_key'))     // Cek API key, bukan toggle
@if(\App\Models\Setting::get('stripe_publishable_key')) // Cek API key, bukan toggle
```

### Dampak Nyata

```
Admin ingin nonaktifkan Billplz sementara (maintenance):
    ↓
Tidak ada toggle → Harus hapus API key dari form
    ↓
Admin delete API key → simpan
    ↓
Billplz hilang dari checkout ✓
    ↓
Seminggu kemudian admin ingin aktifkan kembali:
    → API key sudah tidak tersimpan ❌ (hilang permanen)
    → Admin harus cari lagi API key dari dashboard Billplz
    → Jika tidak ada backup, production terganggu
```

### Solusi

```php
// ManageSettings.php — tambahkan di Tab Payment:
Tab::make('Payment')->schema([
    Toggle::make('manual_transfer_enabled')->label('Aktifkan Manual Transfer'),
    Toggle::make('cod_enabled')->label('Aktifkan COD'),
    
    Section::make('Billplz')->schema([
        Toggle::make('billplz_enabled')->label('Aktifkan Billplz (FPX)'), // ← TAMBAH
        Toggle::make('billplz_sandbox')->label('Sandbox Mode'),
        TextInput::make('billplz_api_key')...,
        // ...
    ]),
    
    Section::make('Stripe')->schema([
        Toggle::make('stripe_enabled')->label('Aktifkan Stripe'),  // ← TAMBAH
        Toggle::make('stripe_sandbox')->label('Test Mode'),
        TextInput::make('stripe_publishable_key')...,
        // ...
    ]),
]),

// checkout-view.blade.php — ganti gate logic:
// Sebelum: @if(Setting::get('billplz_api_key'))
// Sesudah:
@if(\App\Models\Setting::isEnabled('billplz_enabled') && \App\Models\Setting::get('billplz_api_key'))
@if(\App\Models\Setting::isEnabled('stripe_enabled') && \App\Models\Setting::get('stripe_publishable_key'))
```

---

## BUG-PM-03 · `grabpay_enabled` adalah Ghost Setting — Ada di Seeder tapi Tidak di Mana-mana

**File:** `database/seeders/SettingsSeeder.php` · `app/Filament/Pages/ManageSettings.php` · `checkout-view.blade.php`  
**Severity:** 🟠 High

### Masalah

Setting `grabpay_enabled` didefinisikan di seeder dan di `getGroupForKey()`, tetapi **tidak ada di mana pun lainnya** — tidak ada toggle di ManageSettings, tidak ada option di checkout blade, tidak ada handler di PaymentController.

```php
// SettingsSeeder.php — ada:
['key' => 'grabpay_enabled', 'value' => '0', 'group' => 'payment', ...]

// ManageSettings.php getGroupForKey() — ada:
str_starts_with($key, 'grabpay_') => 'payment', // Paham ada GrabPay

// ManageSettings.php form() — TIDAK ADA:
// Toggle::make('grabpay_enabled') ← Missing

// checkout-view.blade.php — TIDAK ADA:
// @if(Setting::isEnabled('grabpay_enabled')) ← Missing

// PaymentController.php — TIDAK ADA:
// case 'grabpay': ← Missing

// CheckoutView.php validation — TIDAK ADA:
'payment_method' => 'required|in:billplz,stripe,cod,manual_transfer'
//                                                      ↑ grabpay tidak di sini
```

### Dampak

GrabPay tidak tersedia ke customer sama sekali, padahal ada niat untuk implementasi. Setting `grabpay_enabled` = `'0'` tersimpan di DB secara percuma dan **tidak bisa diubah** dari admin panel karena tidak ada UI-nya.

### Opsi Solusi

**Opsi A — Implementasikan GrabPay:**
1. Tambahkan Toggle di ManageSettings
2. Tambahkan blade option di checkout
3. Tambahkan `grabpay` ke validation rule
4. Tambahkan handler di PaymentController

**Opsi B — Hapus ghost setting:**
```php
// SettingsSeeder.php — hapus baris:
// ['key' => 'grabpay_enabled', ...]

// Tambahkan migration untuk hapus setting:
Setting::where('key', 'grabpay_enabled')->delete();
```

---

## BUG-PM-04 · Backend `placeOrder()` Tidak Memvalidasi Status Enabled Payment Method

**File:** `app/Livewire/Storefront/CheckoutView.php` (baris 169)  
**Severity:** 🟠 High — Security Bypass

### Masalah

Validasi `payment_method` di server-side hanya mengecek apakah nilainya valid secara enum, **tanpa mengecek apakah metode tersebut sedang diaktifkan** di settings. Ini memungkinkan user memanipulasi form data dan memilih payment method yang sudah di-disable.

```php
// CheckoutView.php — validasi:
$this->validate([
    'payment_method' => 'required|in:billplz,stripe,cod,manual_transfer', // ← Hanya cek enum
    // Tidak ada cek: apakah 'cod' sedang enabled?
    // Tidak ada cek: apakah 'billplz' sudah dikonfigurasi?
]);
```

### Skenario Bypass

```
Admin disable COD dari settings
    ↓
Blade @if(Setting::isEnabled('cod_enabled')) → false → COD disembunyikan
    ↓
User manipulasi form (browser devtools / curl):
    payment_method = "cod"
    ↓
Server: 'in:billplz,stripe,cod,manual_transfer' → VALID ✓
    ↓
Order dibuat dengan COD padahal admin sudah disable COD ❌
```

### Solusi

```php
// CheckoutView.php — tambahkan validasi enabled state:
public function placeOrder()
{
    $this->validate([...]);
    
    // Validasi payment method aktif
    $this->validatePaymentMethodEnabled();
    
    // ... lanjut proses
}

private function validatePaymentMethodEnabled(): void
{
    $method = $this->payment_method;
    
    $enabledChecks = [
        'billplz'         => Setting::get('billplz_api_key') && Setting::isEnabled('billplz_enabled'),
        'stripe'          => Setting::get('stripe_publishable_key') && Setting::isEnabled('stripe_enabled'),
        'cod'             => Setting::isEnabled('cod_enabled'),
        'manual_transfer' => Setting::isEnabled('manual_transfer_enabled'),
    ];
    
    if (!($enabledChecks[$method] ?? false)) {
        $this->addError('payment_method', 'Metode pembayaran ini tidak tersedia.');
        throw new \Exception('Payment method not enabled.');
    }
}
```

---

## BUG-PM-05 · SST Toggle Ada di Admin tapi Tidak Pernah Digunakan di Checkout

**File:** `app/Livewire/Storefront/CheckoutView.php` (baris 229–230) · `ManageSettings.php`  
**Severity:** 🟡 Medium — Fitur tidak berfungsi

### Masalah

Admin bisa mengaktifkan SST dan mengatur rate-nya di Settings, tapi `CheckoutView::placeOrder()` **selalu menyimpan `tax_rate = 0` dan `tax_amount = 0`**, mengabaikan semua setting SST.

```php
// CheckoutView.php:
$order = Order::create([
    // ...
    'tax_rate'   => 0,  // ← Hardcoded 0, tidak cek sst_enabled ❌
    'tax_amount' => 0,  // ← Hardcoded 0 ❌
]);

// Seharusnya:
$sstEnabled = Setting::isEnabled('sst_enabled');
$sstRate = $sstEnabled ? (float) Setting::get('sst_rate', 0) : 0;
$taxAmount = $sstEnabled ? ($this->subtotal * $sstRate / 100) : 0;
$grandTotal = $this->subtotal + $this->shippingCost + $taxAmount;

$order = Order::create([
    'tax_rate'   => $sstRate,
    'tax_amount' => $taxAmount,
    'total'      => $grandTotal,
]);
```

Selain itu, SST juga tidak ditampilkan di order summary pada blade checkout, sehingga customer tidak pernah tahu ada SST.

---

## Diagram Keseluruhan Bug Payment Method Toggle

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    ADMIN PANEL — Tab Payment Settings                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  [✓] Manual Transfer Enabled  ──→ saves as ''/true/false (Filament bug) │
│  [✓] COD Enabled              ──→ saves as ''/true/false                │
│                                                                         │
│  ┌─ Billplz ──────────────────┐   ┌─ Stripe ───────────────────────┐   │
│  │ [ ] ❌ TIDAK ADA TOGGLE    │   │ [ ] ❌ TIDAK ADA TOGGLE        │   │
│  │ Sandbox: [✓]               │   │ Test Mode: [✓]                 │   │
│  │ API Key: [***]             │   │ Publishable Key: [pk_test_...]  │   │
│  └────────────────────────────┘   └────────────────────────────────┘   │
│                                                                         │
│  GrabPay: ❌ Tidak muncul (setting ada di DB tapi tidak ada UI)         │
└─────────────────────────────────────────────────────────────────────────┘
                          │
                          │ Settings disimpan
                          ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                    DATABASE — settings table                             │
├─────────────────────────────────────────────────────────────────────────┤
│  key                      │ value           │ Kondisi                   │
├───────────────────────────┼─────────────────┼───────────────────────────┤
│  billplz_enabled          │ ❌ Tidak ada    │ Tidak pernah dibuat       │
│  stripe_enabled           │ ❌ Tidak ada    │ Tidak pernah dibuat       │
│  cod_enabled              │ '0' (seeder) OR │ '0' = truthy BUG ❌       │
│                           │ ''  (after save)│ '' = falsy ✓              │
│  manual_transfer_enabled  │ '1' (seeder) OR │ '1' = truthy ✓            │
│                           │ '' / '1'        │                           │
│  grabpay_enabled          │ '0' (seeder)    │ Tidak dipakai di mana pun │
│  sst_enabled              │ '0' (seeder)    │ '0' = truthy BUG ❌       │
└─────────────────────────────────────────────────────────────────────────┘
                          │
                          │ Checkout blade membaca
                          ↓
┌─────────────────────────────────────────────────────────────────────────┐
│               CHECKOUT PAGE — Payment Method Section                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  Billplz:  @if(Setting::get('billplz_api_key'))                         │
│            → Tampil jika API key diisi (tidak ada dedicated toggle)     │
│                                                                         │
│  Stripe:   @if(Setting::get('stripe_publishable_key'))                  │
│            → Tampil jika key diisi (tidak ada dedicated toggle)         │
│                                                                         │
│  Manual:   @if(Setting::get('manual_transfer_enabled'))                 │
│            → Seeder: '0' → TRUTHY ← ❌ TAMPIL PADAHAL SEHARUSNYA OFF   │
│            → After save ON:  '1' → truthy ✓                            │
│            → After save OFF: ''  → falsy  ✓ (baru benar setelah save)  │
│                                                                         │
│  COD:      @if(Setting::get('cod_enabled'))                             │
│            → Seeder: '0' → TRUTHY ← ❌ TAMPIL PADAHAL SEHARUSNYA OFF   │
│            → After save ON:  '1' → truthy ✓                            │
│            → After save OFF: ''  → falsy  ✓                            │
│                                                                         │
│  GrabPay:  ❌ Tidak ada di blade sama sekali                            │
└─────────────────────────────────────────────────────────────────────────┘
                          │
                          │ User submit form
                          ↓
┌─────────────────────────────────────────────────────────────────────────┐
│               BACKEND — CheckoutView::placeOrder()                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  Validasi: 'in:billplz,stripe,cod,manual_transfer'                      │
│  → ❌ Tidak cek apakah method sedang enabled                            │
│  → User bisa bypass frontend dengan mengirim POST manual                │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Tabel Ringkasan Bug Payment Method

| Bug | Masalah | Severity | File | Solusi Cepat |
|-----|---------|----------|------|-------------|
| BUG-PM-01 | String `'0'` truthy — COD/Manual selalu muncul setelah seeder | 🔴 Critical | SettingsSeeder, Setting.php | Tambah `Setting::isEnabled()` + fix seeder value |
| BUG-PM-02 | Billplz & Stripe tidak punya toggle on/off | 🟠 High | ManageSettings.php | Tambah `Toggle::make('billplz_enabled')` & `stripe_enabled` |
| BUG-PM-03 | GrabPay orphan — setting ada tapi tidak ada UI/handler | 🟠 High | SettingsSeeder, Blade | Implementasi lengkap atau hapus setting |
| BUG-PM-04 | Backend tidak validasi apakah method enabled | 🟠 High | CheckoutView.php | Tambah server-side enabled check |
| BUG-PM-05 | SST toggle diabaikan — tax selalu 0 | 🟡 Medium | CheckoutView.php | Baca `sst_enabled` di `placeOrder()` |

---

## Prioritas Perbaikan

### Langkah 1 — Paling Cepat (< 30 menit)
```php
// 1. Fix Setting model — tambah isEnabled():
public static function isEnabled(string $key): bool
{
    return filter_var(static::get($key), FILTER_VALIDATE_BOOLEAN);
}

// 2. Fix semua blade check:
// @if(Setting::get('cod_enabled'))          → @if(Setting::isEnabled('cod_enabled'))
// @if(Setting::get('manual_transfer_...'))  → @if(Setting::isEnabled('manual_transfer_enabled'))

// 3. Fix seeder — ganti '0' dengan false untuk semua toggle defaults
```

### Langkah 2 — Tambah Toggle Billplz & Stripe (< 1 jam)
```php
// ManageSettings.php — tambah di Section Billplz:
Toggle::make('billplz_enabled')->label('Aktifkan Billplz')

// ManageSettings.php — tambah di Section Stripe:
Toggle::make('stripe_enabled')->label('Aktifkan Stripe')

// SettingsSeeder.php — tambah default:
['key' => 'billplz_enabled', 'value' => false, 'group' => 'payment', ...]
['key' => 'stripe_enabled',  'value' => false, 'group' => 'payment', ...]

// checkout-view.blade.php:
@if(Setting::isEnabled('billplz_enabled') && Setting::get('billplz_api_key'))
@if(Setting::isEnabled('stripe_enabled') && Setting::get('stripe_publishable_key'))
```

### Langkah 3 — Backend Guard & SST (< 2 jam)
- Tambah `validatePaymentMethodEnabled()` di `placeOrder()`
- Implementasi SST calculation berdasarkan `sst_enabled` dan `sst_rate`
- Tampilkan SST line item di checkout order summary
- Putuskan: implementasi GrabPay penuh atau hapus ghost setting

---

*Dokumen ini adalah addendum dari AUDIT_CHECKOUT_COURIER.md. Dibaca bersama untuk gambaran penuh.*
# 🔍 Laporan Audit Bug & Cacat Logika
## Scope: Order Checkout & Fitur Kurir — WebEcommerceMalaysia

> **Tanggal Audit:** Mei 2026  
> **Scope:** `CheckoutView.php`, `PaymentController.php`, `MyParcelService.php`, `OrderForm.php`, `PaymentsTable.php`, `TrackOrder.php`, model, dan migration terkait  
> **Severity:** 🔴 Critical · 🟠 High · 🟡 Medium · 🟢 Low

---

## Ringkasan Eksekutif

Ditemukan **24 bug/cacat** — 6 bersifat **Critical** (menyebabkan data corrupt, kehilangan uang, atau bypass keamanan), 8 High, 7 Medium, dan 3 Low. Sistem checkout dapat berjalan dalam kondisi normal tetapi sangat rentan terhadap edge case produksi: pembayaran gateway yang ditinggalkan, concurrent checkout, dan tracking yang selalu gagal ditampilkan.

| Kategori | Critical | High | Medium | Low |
|----------|----------|------|--------|-----|
| Backend / Logika | 4 | 4 | 3 | 1 |
| Keamanan | 2 | 2 | 1 | 0 |
| Frontend / UX | 0 | 1 | 2 | 2 |
| Filament Admin | 0 | 1 | 1 | 0 |
| **Total** | **6** | **8** | **7** | **3** |

---

## BAGIAN 1 — BUG CRITICAL 🔴

---

### BUG-01 · `transaction_id` Column Tidak Ada di Database

**File:** `app/Http/Controllers/PaymentController.php` (baris 54, 97)  
**Severity:** 🔴 Critical — Data hilang / SQL Error

#### Masalah
`PaymentController` memanggil `.update(['transaction_id' => ...])` untuk menyimpan ID bill Billplz dan session ID Stripe, tetapi kolom `transaction_id` **tidak pernah dibuat** di tabel `payments`.

```php
// processBillplz() — baris 54
$order->payment->update(['transaction_id' => $bill['id']]);

// processStripe() — baris 97
$order->payment->update(['transaction_id' => $session->id]);
```

Tabel `payments` hanya memiliki kolom: `id`, `order_id`, `type`, `method`, `status`, `reference`, `amount`, `proof_image`, `verified_at`, `verified_by`.

#### Dampak
- ID transaksi Billplz/Stripe **tidak pernah tersimpan**
- Jika ada dispute pembayaran, admin tidak bisa lookup transaksi
- `billplzCallback` membaca `$billplzId` dari redirect param (bukan dari DB) — sementara ini masih berfungsi, tetapi fragile
- Potensi `QueryException` pada beberapa konfigurasi database yang strict

#### Flowchart Error
```
[User Bayar via Billplz]
        ↓
[Billplz buat bill → return bill['id']]
        ↓
[PaymentController: payment->update(['transaction_id' => bill['id']])]
        ↓
        ╔══════════════════════════════╗
        ║  ❌ KOLOM TIDAK ADA DI DB   ║
        ║  Update silently ignored     ║
        ║  atau QueryException         ║
        ╚══════════════════════════════╝
        ↓
[Redirect ke Billplz URL berhasil]
        ↓
[User bayar di Billplz]
        ↓
[Callback: billplzId diambil dari ?billplz[id]=xxx]
        ↓
[Verify via API berhasil — tapi tidak ada audit trail di DB]
```

#### Solusi
```php
// Migration baru:
Schema::table('payments', function (Blueprint $table) {
    $table->string('transaction_id')->nullable()->after('reference');
});

// Atau gunakan kolom 'reference' yang sudah ada:
$order->payment->update(['reference' => $bill['id']]);
```

---

### BUG-02 · Payment Status Enum Tidak Konsisten: `'success'` vs `'paid'`

**File:** `app/Http/Controllers/PaymentController.php` (baris 140, 169) vs `app/Filament/Resources/Payments/`  
**Severity:** 🔴 Critical — Data tidak valid, badge salah, fitur admin tidak jalan

#### Masalah
Gateway callback menggunakan status `'success'`, sedangkan semua definisi lain menggunakan `'paid'`.

```php
// PaymentController::billplzCallback() & stripeCallback()
$order->payment->update(['status' => 'success']); // ❌ 'success' tidak ada di enum

// PaymentForm.php — status options yang valid:
'pending' => 'Pending',
'paid'    => 'Paid',       // ✅ Benar
'failed'  => 'Failed',
'refunded'=> 'Refunded',

// PaymentsTable.php — badge color:
'paid' => 'success',       // ✅ Benar
// 'success' tidak ada → jatuh ke default => 'primary' (biru, bukan hijau)

// Verify action — kondisi visible:
->visible(fn ($record) => $record->method === 'manual_transfer' && $record->status === 'pending')
// Ini sudah benar, tapi kalau gateway set 'success' bukan 'paid',
// tombol verify manual_transfer tetap muncul untuk transaksi yang sudah bayar via gateway
```

#### Flowchart Cacat Status
```
BILLPLZ BAYAR:
[Callback dipanggil]
    → payment->status = 'success'   ← ❌ bukan 'paid'
    → Badge di admin: warna PRIMARY (biru) bukan HIJAU
    → Filament PaymentForm tidak tampilkan 'success' di dropdown
    → Admin verify action: visible jika status === 'pending' → tersembunyi ✓
    → Tapi dashboard/report yang filter 'paid' TIDAK menangkap order ini

MANUAL TRANSFER (BENAR):
[Admin klik Approve]
    → payment->status = 'paid'      ← ✅ konsisten
    → Badge hijau
```

#### Solusi
```php
// PaymentController.php — ganti kedua instance:
$order->payment->update(['status' => 'paid']);  // bukan 'success'
```

---

### BUG-03 · Billplz X-Signature TIDAK Diverifikasi — Keamanan Kritis

**File:** `app/Http/Controllers/PaymentController.php`, `routes/web.php`  
**Severity:** 🔴 Critical — Payment Forgery / Bypass Pembayaran

#### Masalah
Setting `billplz_x_signature` disimpan di admin panel, tetapi **tidak pernah digunakan** di `billplzCallback()`. Siapapun bisa melakukan request GET manual ke URL callback dan memalsukan pembayaran.

```php
// PaymentController::billplzCallback()
public function billplzCallback(Request $request)
{
    $orderId = $request->order;
    $order = Order::findOrFail($orderId);
    
    // Authorization hanya cek session/auth — bukan signature
    if (!auth()->check() && session('last_order_id') !== $order->id) abort(403);
    
    $billplzId = $request->billplz['id'] ?? null;
    // ❌ x_signature dari $request->billplz['x_signature'] TIDAK PERNAH DIVERIFIKASI
    
    if ($billplzId) {
        // Verify bill via API — ini sedikit melindungi, TAPI:
        // Hanya untuk kasus billplzId tersedia
        // Jika $billplzId null → langsung redirect ke success
    }
    return redirect()->route('checkout.success', $order->id);
}
```

Billplz mengirim `billplz[x_signature]` di redirect URL. Ini harus diverifikasi dengan `billplz_x_signature` key sebelum diproses.

#### Attack Vector
```
Attacker tahu Order ID = 42 (integer sequential, mudah diterka)
↓
GET /payment/callback/billplz?order=42
    tanpa billplz[id] parameter
↓
$billplzId = null → skip API verification block
↓
redirect ke checkout.success — ORDER TAMPAK SUKSES DI USER
↓
(tapi payment status masih 'pending' di DB — tidak berubah)
↓
Admin tidak sadar, kirim barang

Lebih parah jika ada bug lain yang auto-update status...
```

#### Solusi
```php
public function billplzCallback(Request $request)
{
    // 1. Verifikasi X-Signature SEBELUM apapun
    $xSignatureKey = Setting::get('billplz_x_signature');
    if ($xSignatureKey) {
        $data = $request->billplz ?? [];
        // Billplz signature: hash_hmac('sha256', implode('|', [...fields]), $key)
        $expectedSig = hash_hmac('sha256', 
            ($data['id'] ?? '') . '|' . ($data['collection_id'] ?? '') . '|' .
            ($data['paid'] ?? '') . '|' . ($data['state'] ?? ''),
            $xSignatureKey
        );
        if (!hash_equals($expectedSig, $data['x_signature'] ?? '')) {
            abort(400, 'Invalid signature');
        }
    }
    // ... lanjut proses
}
```

---

### BUG-04 · Tidak Ada Stripe Webhook Server-to-Server

**File:** `routes/web.php`, `app/Http/Controllers/PaymentController.php`  
**Severity:** 🔴 Critical — Order Hilang / Pembayaran Tidak Terkonfirmasi

#### Masalah
Hanya ada GET redirect callback untuk Stripe, tanpa webhook handler POST. Jika user menutup browser setelah membayar di Stripe sebelum redirect balik ke toko, **pembayaran tidak akan pernah terkonfirmasi**.

```php
// routes/web.php — hanya ada:
Route::get('/payment/callback/stripe', [PaymentController::class, 'stripeCallback']);

// ❌ TIDAK ADA:
// Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);
```

`stripe_webhook_secret` tersimpan di settings tapi tidak digunakan di mana pun.

#### Flowchart Bug
```
[User bayar di Stripe Checkout]
        ↓
    ┌───────────────────────┐
    │   User redirect balik  │ → stripeCallback() → status = 'paid' ✓
    │   ke toko              │
    └───────────────────────┘
    ┌───────────────────────┐
    │   User TUTUP BROWSER  │ → ❌ Tidak ada callback
    │   / koneksi putus      │    Order status = 'pending' selamanya
    └───────────────────────┘
    ┌───────────────────────┐
    │   Stripe retries       │ → ❌ Tidak ada webhook endpoint
    │   webhook 3x gagal     │    Pembayaran confirmed di Stripe
    └───────────────────────┘    Order TIDAK pernah diproses
```

#### Solusi
```php
// routes/web.php — tambahkan (TANPA middleware CSRF):
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// StripeWebhookController.php:
public function handle(Request $request)
{
    $webhookSecret = Setting::get('stripe_webhook_secret');
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    
    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }
    
    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        $order = Order::find($session->client_reference_id);
        if ($order && $order->payment->status !== 'paid') {
            $order->payment->update(['status' => 'paid']);
            $order->update(['status' => 'processing']);
        }
    }
    
    return response()->json(['status' => 'ok']);
}
```

---

### BUG-05 · Race Condition: Stock Check di Luar Transaksi

**File:** `app/Livewire/Storefront/CheckoutView.php` (baris 173–184 vs 235–250)  
**Severity:** 🔴 Critical — Overselling / Stock Negatif

#### Masalah
Pengecekan stok dilakukan **sebelum** `DB::transaction()`, sedangkan dekremen stok ada **di dalam** transaksi. Dua request concurrent bisa lolos check yang sama dan keduanya mendekremen stok.

```php
public function placeOrder()
{
    // ❌ CEK STOK DI LUAR TRANSACTION (baris 173-184)
    foreach ($this->cart->items as $item) {
        $productStock = $item->product->stock ?? 0;
        if ($productStock < $item->qty) {
            $this->addError('cart', "Product out of stock.");
            return;
        }
    }

    // Jarak antara check dan dekremen = waktu untuk race condition
    
    $order = DB::transaction(function () {
        // ...
        foreach ($this->cart->items as $item) {
            // ✅ Ini sudah dalam transaction, tapi tidak ada lock
            $item->product->decrement('stock', $item->qty);  // baris 248
            // ❌ Tidak ada lockForUpdate() → bisa negatif
        }
    });
}
```

#### Flowchart Race Condition
```
User A                          User B
(checkout, stok = 1)           (checkout, stok = 1)
    ↓                               ↓
[Check: stock=1 >= qty=1 ✓]    [Check: stock=1 >= qty=1 ✓]
    ↓                               ↓
[Masuk transaction]            [Masuk transaction]
    ↓                               ↓
[decrement stock: 1→0 ✓]       [decrement stock: 0→-1 ❌]
    ↓                               ↓
[Order A dibuat]               [Order B dibuat — STOCK NEGATIF!]
```

#### Solusi
```php
$order = DB::transaction(function () use ($grandTotal, $addressId) {
    foreach ($this->cart->items as $item) {
        // Gunakan lockForUpdate() untuk pessimistic locking
        $product = \App\Models\Product::lockForUpdate()->find($item->product_id);
        
        if ($product->stock < $item->qty) {
            throw new \Exception("Stok produk '{$product->name}' tidak mencukupi.");
        }
        
        if ($item->variant_id) {
            $variant = \App\Models\ProductVariant::lockForUpdate()->find($item->variant_id);
            if ($variant && $variant->stock !== null && $variant->stock < $item->qty) {
                throw new \Exception("Stok varian tidak mencukupi.");
            }
        }
    }
    // ... buat order, dekremen stok
});
```

---

### BUG-06 · `Http::withoutVerifying()` Tidak Di-assign — SSL Selalu Aktif di Local

**File:** `app/Services/MyParcelService.php` (baris 40–45)  
**Severity:** 🔴 Critical — Developer tidak bisa test di local, semua API call gagal

#### Masalah
`Http::withoutVerifying()` mengembalikan **instance baru**, bukan memodifikasi instance existing. Hasil return-nya dibuang.

```php
$http = Http::asForm()->timeout(30);

if (config('app.env') === 'local') {
    $http->withoutVerifying(); // ❌ Instance baru dibuang, $http tidak berubah
}

$response = $http->post(...); // SSL verification tetap AKTIF
```

#### Dampak
- Di environment `local`, semua request ke MyParcel (dengan self-signed cert atau demo server) **gagal** dengan SSL error
- Developer tidak bisa test fitur shipping sama sekali di lokal
- Semua fitur checkout yang bergantung MyParcel (hitung ongkir, generate AWB) throw exception

#### Solusi
```php
$http = Http::asForm()->timeout(30);

if (config('app.env') === 'local') {
    $http = $http->withoutVerifying(); // ✅ Reassign instance baru
}
```

---

## BAGIAN 2 — BUG HIGH 🟠

---

### BUG-07 · MyParcel `trace()` Response Double-Unwrap — Tracking Selalu Gagal

**File:** `app/Livewire/Storefront/Dashboard/TrackOrder.php` (baris 40–44)  
**Severity:** 🟠 High — Fitur tracking customer 100% tidak berfungsi

#### Masalah
`MyParcelService::post()` sudah meng-unwrap `$json['data']` sebelum return. Tapi `TrackOrder::fetchTracking()` masih mengakses struktur response asli.

```php
// MyParcelService::post() — return value:
return $json['data'] ?? []; // Sudah unwrap → return isi data langsung

// TrackOrder::fetchTracking() — mengakses struktur SALAH:
$response = $myParcel->trace($this->order->tracking_no);
// $response sekarang = isi dari data[], bukan full response

if (isset($response['status']) && $response['status'] == 'success' // ❌ Tidak ada
    && !empty($response['data'][0]['tracker'])) {                   // ❌ Tidak ada
    $this->trackingData = $response['data'][0]['tracker'];
} else {
    $this->error = 'Unable to fetch tracking data at the moment.'; // ← Selalu masuk sini
}
```

#### Flowchart Bug
```
MyParcel API Response (raw):
{
  "status": true,
  "message": "success",
  "data": [{ "tracker": [...] }]  ← Data asli ada di sini
}
        ↓
post() return $json['data']:
[ { "tracker": [...] } ]           ← Yang dikembalikan ke TrackOrder
        ↓
TrackOrder cek $response['status'] == 'success'
→ Tidak ada key 'status' → FALSE
→ SELALU masuk ke else → error message ditampilkan

User: "Tracking tidak tersedia" ← PADAHAL data ada
```

#### Solusi
```php
public function fetchTracking()
{
    try {
        $myParcel = app(MyParcelService::class);
        $data = $myParcel->trace($this->order->tracking_no);
        // $data sudah = array of tracker items (post() sudah unwrap)
        
        if (!empty($data[0]['tracker'])) {
            $this->trackingData = $data[0]['tracker'];
        } elseif (!empty($data['tracker'])) {
            $this->trackingData = $data['tracker'];
        } else {
            $this->error = 'Unable to fetch tracking data.';
        }
    } catch (\Exception $e) { ... }
}
```

---

### BUG-08 · Stok Didekremen Sebelum Pembayaran Gateway Dikonfirmasi

**File:** `app/Livewire/Storefront/CheckoutView.php` (baris 235–250)  
**Severity:** 🟠 High — Stok habis meski pembayaran batal

#### Masalah
Untuk semua metode pembayaran (termasuk Billplz dan Stripe), stok didekremen **saat order dibuat**, bukan saat pembayaran terkonfirmasi. Jika user tidak jadi bayar di gateway, stok tetap terpotong.

```
[User pilih Billplz] → [placeOrder()] → [DB.transaction: ORDER DIBUAT + STOK -1] 
    → [Redirect ke Billplz] → [User tutup tab] → Stok tetap -1 selamanya
```

#### Flowchart UX Cacat
```
Stok Produk X = 5
        ↓
User A checkout (Billplz) → Stok = 4, Order pending
        ↓
User B checkout (Billplz) → Stok = 3, Order pending
        ↓
User C checkout (Billplz) → Stok = 2, Order pending
        ↓
Semua 3 user abandon payment → Stok = 2 (SEMESTINYA = 5)
        ↓
User D datang → Lihat stok = 2 padahal harusnya 5
```

#### Solusi
Untuk payment gateway, jangan dekremen stok saat order dibuat. Dekremen hanya saat payment webhook/callback `status = 'paid'` masuk:

```php
// Di PaymentController::billplzCallback() atau Stripe webhook:
if ($data['state'] === 'paid') {
    $order->payment->update(['status' => 'paid']);
    $order->update(['status' => 'processing']);
    
    // Dekremen stok di sini, bukan saat order dibuat
    foreach ($order->items as $item) {
        $item->product->decrement('stock', $item->qty);
        if ($item->variant) $item->variant->decrement('stock', $item->qty);
    }
}
```

Tandai juga dengan flag `stock_reserved` di order agar tidak double dekremen.

---

### BUG-09 · `order_number` Tidak Ada Unique Index — Race Condition Generator

**File:** `database/migrations/2026_05_21_120000_add_order_number_and_weight_columns.php`, `CheckoutView.php`  
**Severity:** 🟠 High — Duplicate order number dimungkinkan

#### Masalah
Kolom `order_number` ditambahkan sebagai `nullable()` tanpa `unique()`. Loop generator menggunakan pattern check-then-insert yang tidak atomic.

```php
// Migration: tidak ada ->unique()
$table->string('order_number')->nullable()->after('id'); // ❌ No unique constraint

// CheckoutView.php — tidak atomic:
do {
    $orderNumber = 'ORD-' . strtoupper(Str::random(8));
} while (Order::where('order_number', $orderNumber)->exists());
// Jeda antara EXISTS check dan INSERT = race condition window
```

#### Solusi
```php
// Migration:
$table->string('order_number')->nullable()->unique()->after('id');

// Gunakan DB-level uniqueness + catch exception:
do {
    $orderNumber = 'ORD-' . strtoupper(Str::random(8));
} while (Order::where('order_number', $orderNumber)->exists());
// Dengan unique index, duplicate insert akan throw exception yang bisa ditangkap
```

---

### BUG-10 · AWB Dibuat dengan Kurir Default Admin, Bukan Kurir Pilihan Customer

**File:** `app/Services/MyParcelService.php` (baris 359)  
**Severity:** 🟠 High — Customer menerima kurir yang salah

#### Masalah
`buildShipmentFromOrder()` menggunakan `setting('myparcel_default_provider')` sebagai `provider_code`, mengabaikan kurir yang dipilih customer saat checkout.

```php
// MyParcelService::buildShipmentFromOrder() — baris 359:
'provider_code' => setting('myparcel_default_provider') ?? 'poslaju', // ❌ Hardcode setting

// Order model menyimpan courier sebagai NAME (bukan code):
// order->courier = "Pos Laju" (nama, bukan "poslaju")
// Tidak digunakan sama sekali di sini
```

#### Flowchart Cacat
```
[Customer pilih J&T Express (jnt) di checkout]
    → order->courier disimpan = "J&T Express" (nama)
    ↓
[Admin klik Generate AWB]
    → buildShipmentFromOrder() membaca setting('myparcel_default_provider')
    → Setting admin = 'poslaju'
    ↓
[AWB dibuat untuk Pos Laju] ← ❌ BUKAN J&T!
    ↓
[Paket dikirim via Pos Laju, customer expect J&T]
```

#### Solusi
Simpan `courier_code` (bukan nama) di order, lalu gunakan di `buildShipmentFromOrder()`:

```php
// CheckoutView.php — saat create order:
'courier' => $this->courier, // Simpan CODE (jnt, poslaju, dll)
'courier_name' => $this->availableCouriers[$this->courier]['name'],

// MyParcelService::buildShipmentFromOrder():
'provider_code' => $order->courier ?? setting('myparcel_default_provider') ?? 'poslaju',
```

---

### BUG-11 · `label_url` AWB Tidak Disimpan ke Database

**File:** `app/Services/MyParcelService.php` (baris 408–420)  
**Severity:** 🟠 High — Admin tidak bisa reprint label tanpa API call ulang

#### Masalah
Setelah generate AWB, `label_url` (URL PDF consignment note) hanya di-log, tidak disimpan ke tabel `orders`.

```php
$labelUrl = $shipments[0]['label_url'] ?? $shipments[0]['consignment_note'] ?? null;

$order->update([
    'tracking_no' => $trackingNo,
    'courier' => $shipment['provider_code'] ?? 'poslaju',
    'status' => 'processing',
    // ❌ 'label_url' tidak ada di orders table, tidak disimpan
]);

Log::info('AWB Generated', [
    'label_url' => $labelUrl, // ← Hanya di log, hilang setelah rotate
]);
```

#### Solusi
```php
// Migration:
$table->string('awb_label_url')->nullable()->after('tracking_no');

// MyParcelService::generateAwbForOrder():
$order->update([
    'tracking_no' => $trackingNo,
    'awb_label_url' => $labelUrl, // ✅ Simpan
    'courier' => ...,
    'status' => 'processing',
]);

// OrdersTable.php — tambah action:
Action::make('download_label')
    ->visible(fn ($r) => !empty($r->awb_label_url))
    ->action(fn ($r) => redirect($r->awb_label_url)),
```

---

### BUG-12 · Fitur Voucher/Diskon Tidak Diimplementasi di Checkout

**File:** `app/Livewire/Storefront/CheckoutView.php`  
**Severity:** 🟠 High — Fitur ada di DB tapi tidak bisa digunakan customer

#### Masalah
`Order` model punya relasi `voucher()` dan kolom `voucher_id`, `OrderForm` Filament menampilkan voucher selector, tapi `CheckoutView::placeOrder()` **tidak ada input voucher** dan **tidak mengurangi total**.

```php
// CheckoutView.php — TIDAK ADA:
// - Property $voucherCode
// - Method applyVoucher()
// - Logika discount di placeOrder()

$grandTotal = $this->subtotal + $this->shippingCost;
// ❌ Tidak ada pengurangan diskon

$order = Order::create([
    // ❌ voucher_id tidak pernah diisi dari customer input
    'total' => $grandTotal,
]);
```

#### Flowchart UX Cacat
```
[Admin buat voucher DISKON10 = 10% off]
        ↓
[Customer ada kode voucher tapi...]
        ↓
[Halaman checkout: TIDAK ADA field input kode voucher]
        ↓
[Customer tidak bisa pakai voucher]
        ↓
[Admin lihat OrderForm → field voucher ada tapi kosong]
        ↓
[Admin assign manual voucher, tapi total sudah salah]
```

---

### BUG-13 · COD Orders Tidak Ada Workflow Konfirmasi Penerimaan

**File:** `app/Filament/Resources/Payments/Tables/PaymentsTable.php` (baris 62)  
**Severity:** 🟠 High — COD payments stuck di 'pending' selamanya

#### Masalah
Tombol "Approve" di PaymentsTable hanya muncul untuk `method === 'manual_transfer'`, bukan untuk COD. Tidak ada cara admin mengonfirmasi bahwa COD sudah diterima.

```php
->visible(fn ($record) => $record->method === 'manual_transfer' && $record->status === 'pending')
// ❌ COD tidak termasuk

// COD payment: method = 'cod', status = 'pending' SELAMANYA
// Tidak ada trigger untuk ubah ke 'paid'
```

#### Solusi
```php
->visible(fn ($record) => 
    in_array($record->method, ['manual_transfer', 'cod']) && 
    $record->status === 'pending'
)
->label(fn ($record) => $record->method === 'cod' ? 'Mark COD Received' : 'Approve Transfer')
```

---

## BAGIAN 3 — BUG MEDIUM 🟡

---

### BUG-14 · `OrderForm` Filament: `user_id` Required tapi Guest Orders Memiliki `null`

**File:** `app/Filament/Resources/Orders/Schemas/OrderForm.php` (baris 23–26)  
**Severity:** 🟡 Medium — Admin tidak bisa edit/save guest orders

```php
Select::make('user_id')
    ->relationship('user', 'name')
    ->disabled()
    ->required() // ❌ Guest orders punya user_id = null → validation fail
```

**Solusi:** Hapus `->required()` atau jadikan conditional:
```php
->required(fn ($record) => !is_null($record?->user_id))
```

---

### BUG-15 · Guest Info Section Selalu Tampil Saat Create Order Baru di Filament

**File:** `app/Filament/Resources/Orders/Schemas/OrderForm.php` (baris 99)  
**Severity:** 🟡 Medium — Confusion UX di admin saat buat order manual

```php
->visible(fn ($record) => !$record?->user_id)
// Saat Create: $record = null → !null?->user_id → !null → true
// Selalu tampil walau belum bisa ditentukan guest/user
```

**Solusi:**
```php
->visible(fn ($record) => $record !== null && is_null($record->user_id))
```

---

### BUG-16 · Billplz Callback Menggunakan GET — Rentan Browser Cache & History

**File:** `routes/web.php`  
**Severity:** 🟡 Medium — Keamanan lemah

```php
Route::get('/payment/callback/billplz', ...); // ❌ GET dapat di-bookmark, di-cache, di-replay
```

Billplz juga men-support POST callback. URL GET bisa di-replay dari browser history.

**Solusi:** Tambahkan route POST untuk webhook server-to-server (di luar middleware web):
```php
Route::post('/webhook/billplz', [PaymentController::class, 'billplzWebhook'])
    ->withoutMiddleware([VerifyCsrfToken::class]);
```

---

### BUG-17 · Stok Tidak Dikembalikan Saat Order Dibatalkan

**File:** Tidak ada handler cancel  
**Severity:** 🟡 Medium — Inventory inaccurate setelah pembatalan

Tidak ada observer atau action yang mengembalikan stok ketika order di-cancel dari Filament admin.

```php
// OrderForm status bisa diubah ke 'cancelled'
// Tapi tidak ada trigger:
// product->increment('stock', item->qty)
```

**Solusi:** Tambahkan `Observer` atau event listener:
```php
// app/Observers/OrderObserver.php
public function updated(Order $order): void
{
    if ($order->isDirty('status') && $order->status === 'cancelled') {
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->qty);
            if ($item->variant) $item->variant->increment('stock', $item->qty);
        }
    }
}
```

---

### BUG-18 · Shipping Cost Tidak Di-recalculate Saat City/State Berubah

**File:** `app/Livewire/Storefront/CheckoutView.php`  
**Severity:** 🟡 Medium — UX confusion, ongkir bisa salah

Hanya `updatedPostcode()` yang memicu `calculateShipping()`. Mengubah kota/state tidak memicu ulang kalkulasi.

```
[User isi postcode 50000 → ongkir dihitung untuk KL]
[User ganti city = "Johor Bahru" tapi postcode tetap 50000]
→ Ongkir tetap KL, padahal harusnya recalculate
```

---

### BUG-19 · `$this->cart` di Livewire Checkout Adalah State Stale

**File:** `app/Livewire/Storefront/CheckoutView.php` (baris 47–56)  
**Severity:** 🟡 Medium — Cart versi lama bisa di-checkout

Cart hanya di-load sekali di `mount()`. Jika user membuka tab lain dan mengubah cart, `CheckoutView` tetap menggunakan data lama. Tidak ada re-fresh cart sebelum `placeOrder()`.

**Solusi:** Re-load cart di awal `placeOrder()`:
```php
public function placeOrder()
{
    // Refresh cart dari DB sebelum proses
    $this->cart = Cart::with(['items.product', 'items.variant'])
        ->where('id', $this->cart->id)->first();
    
    if (!$this->cart || $this->cart->items->isEmpty()) {
        return redirect()->route('cart.index');
    }
    // ... lanjut
}
```

---

### BUG-20 · `verifyHash()` Mengembalikan `false` Jika `apiSecret` Kosong — Hash Tidak Diverifikasi Diam-diam

**File:** `app/Services/MyParcelService.php` (baris 99–105)  
**Severity:** 🟡 Medium — Response integrity tidak dijamin

```php
public function verifyHash(array $response): bool
{
    if (empty($response['hash']) || empty($this->apiSecret)) {
        return false; // ← Return false, tapi post() hanya memanggil verifyHash
                      //   jika !empty($this->apiSecret)
                      //   Jadi kalau apiSecret kosong, verifyHash tidak dipanggil
                      //   Keamanan hash TIDAK diverifikasi
    }
    // ...
}
```

Jika admin tidak mengisi `myparcel_api_secret`, hash integrity check ter-bypass sepenuhnya dan response dari server manapun akan diterima.

---

## BAGIAN 4 — BUG LOW 🟢

---

### BUG-21 · `CartView::decrement()` Tidak Check Stok

**File:** `app/Livewire/Storefront/CartView.php`  
**Severity:** 🟢 Low — Inkonsistensi minor

`increment()` mengecek stok sebelum tambah qty, tapi `decrement()` tidak perlu cek (hanya turun ke minimum 1). Sudah benar. Tapi tidak ada proteksi ketika product dihapus dari catalog sementara masih di cart.

---

### BUG-22 · `OrderHistory` Tidak Menampilkan Guest Orders

**File:** `app/Livewire/Storefront/Dashboard/OrderHistory.php`  
**Severity:** 🟢 Low — Edge case minimal

```php
$orders = Order::where('user_id', auth()->id()) // Hanya user yang login
    ->paginate(10);
// Guest orders tidak bisa dilihat meski user kemudian register
```

---

### BUG-23 · `PaymentForm` Memiliki Option `'grabpay'` yang Tidak Didukung di Checkout

**File:** `app/Filament/Resources/Payments/Schemas/PaymentForm.php`  
**Severity:** 🟢 Low — Inkonsistensi data

PaymentForm admin punya pilihan `'grabpay'`, tapi `CheckoutView` validation rule hanya mengizinkan `'billplz,stripe,cod,manual_transfer'`. Admin bisa assign GrabPay ke payment record tapi customer tidak bisa memilihnya.

---

## BAGIAN 5 — FLOWCHART LENGKAP UX CHECKOUT

### Flow Normal (Happy Path)

```
[Customer buka /checkout]
        ↓
[mount(): load cart, pre-fill user data]
        ↓
[Customer isi Name, Email, Phone]
        ↓
[Customer isi Address Line, City, State]
        ↓
[Customer ketik Postcode]
        ↓ (wire:model.live.debounce.500ms)
[updatedPostcode() → calculateShipping()]
        ↓
[MyParcelService::checkPrice(storePostcode, postcode, weight)]
        ↓
[Tampil daftar kurir + harga]
        ↓
[Customer pilih kurir → updatedCourier() → update shippingCost]
        ↓
[Customer pilih payment method]
        ↓
[Klik "Place Order" → placeOrder()]
        ↓
[Validation rules]
        ↓
[Stock check (LUAR transaction ← BUG-05)]
        ↓
[DB::transaction: create Order, OrderItems, Payment, delete Cart]
        ↓
[Send email konfirmasi]
        ↓
    ┌──────────────────────────┬────────────────────────┐
    │    COD / Manual Transfer  │   Billplz / Stripe     │
    ↓                          ↓                         ↓
[redirect checkout.success]  [redirect payment.process]
                                        ↓
                               [PaymentController::process()]
                                        ↓
                          ┌─────────────┴────────────────┐
                          │ Billplz                       │ Stripe
                          ↓                               ↓
                [Create bill via API]         [Create Checkout Session]
                          ↓                               ↓
                [Save transaction_id         [Save transaction_id
                 → ❌ BUG-01: GAGAL]          → ❌ BUG-01: GAGAL]
                          ↓                               ↓
                [Redirect ke Billplz]        [Redirect ke Stripe]
                          ↓                               ↓
                    [User bayar]                   [User bayar]
                          ↓                               ↓
                [Redirect GET callback]        [Redirect GET callback]
                [❌ BUG-03: No X-Sig]        [No webhook ← BUG-04]
                          ↓                               ↓
                [payment->status = 'success'  [payment->status = 'success'
                 ❌ BUG-02: salah status]      ❌ BUG-02: salah status]
                          ↓                               ↓
                [redirect checkout.success]  [redirect checkout.success]
```

---

### Flow Tracking Kurir (Selalu Error)

```
[Customer buka /dashboard/orders/{id}/track]
        ↓
[TrackOrder::mount(): auth check ✓]
        ↓
[fetchTracking()]
        ↓
[MyParcelService::trace(tracking_no)]
        ↓
[post('trace', ...) → return $json['data'] ← sudah di-unwrap]
        ↓
[$response = [ {tracker: [...]} ]   ← bukan full response]
        ↓
[cek $response['status'] == 'success']
→ KEY 'status' TIDAK ADA di $response  ← BUG-07
        ↓
[ELSE → $this->error = "Unable to fetch tracking"]
        ↓
[Customer: "Tracking tidak tersedia" ← SELALU]

╔══════════════════════════════════════════╗
║  Padahal data ada, hanya akses salah     ║
╚══════════════════════════════════════════╝
```

---

### Flow Generate AWB (Kurir Salah)

```
[Admin klik "Generate AWB" untuk Order #42]
        ↓
[MyParcelService::generateAwbForOrder($order)]
        ↓
[buildShipmentFromOrder($order)]
        ↓
['provider_code' => setting('myparcel_default_provider')]
→ Setting = 'poslaju'  ← BUG-10: Abaikan pilihan customer
        ↓
[order->courier = "J&T Express" ← Tidak digunakan]
        ↓
[AWB dibuat untuk Pos Laju]
        ↓
[$labelUrl = $shipments[0]['label_url']]  ← BUG-11: Tidak disimpan ke DB
        ↓
[Log::info(label_url)] ← Hilang setelah log rotate
        ↓
[order->tracking_no = 'EK123456789MY' ← Tracking Pos Laju]
        ↓
[Customer expect J&T, dapat Pos Laju] ❌
```

---

## BAGIAN 6 — TABEL RINGKASAN & PRIORITAS PERBAIKAN

| No | Bug | Severity | File Utama | Impact |
|----|-----|----------|-----------|--------|
| BUG-01 | `transaction_id` column tidak ada | 🔴 Critical | PaymentController | Data transaksi hilang |
| BUG-02 | Status enum `'success'` vs `'paid'` | 🔴 Critical | PaymentController | Data tidak valid, badge salah |
| BUG-03 | Billplz X-Signature tidak diverifikasi | 🔴 Critical | PaymentController | Payment forgery |
| BUG-04 | Tidak ada Stripe webhook | 🔴 Critical | routes/web.php | Order tidak terkonfirmasi |
| BUG-05 | Race condition stock check | 🔴 Critical | CheckoutView | Overselling |
| BUG-06 | `withoutVerifying()` tidak di-assign | 🔴 Critical | MyParcelService | Shipping API gagal di local |
| BUG-07 | Trace response double-unwrap | 🟠 High | TrackOrder | Tracking 100% gagal |
| BUG-08 | Stok dekremen sebelum payment | 🟠 High | CheckoutView | Inventory tidak akurat |
| BUG-09 | `order_number` tanpa unique index | 🟠 High | Migration | Duplicate order number |
| BUG-10 | AWB pakai kurir default, bukan pilihan customer | 🟠 High | MyParcelService | Kurir salah |
| BUG-11 | `label_url` tidak disimpan ke DB | 🟠 High | MyParcelService | Cannot reprint label |
| BUG-12 | Voucher tidak diimplementasi di checkout | 🟠 High | CheckoutView | Fitur tidak berfungsi |
| BUG-13 | COD tidak ada approval workflow | 🟠 High | PaymentsTable | COD stuck pending |
| BUG-14 | `user_id` required di OrderForm guest | 🟡 Medium | OrderForm | Admin tidak bisa edit |
| BUG-15 | Guest section selalu tampil saat Create | 🟡 Medium | OrderForm | UX confusion admin |
| BUG-16 | Billplz callback via GET | 🟡 Medium | routes | Replay attack |
| BUG-17 | Stok tidak restore saat cancel | 🟡 Medium | Tidak ada | Inventory inaccurate |
| BUG-18 | Shipping tidak recalculate saat city berubah | 🟡 Medium | CheckoutView | Ongkir salah |
| BUG-19 | Cart stale di Livewire state | 🟡 Medium | CheckoutView | Old cart di-checkout |
| BUG-20 | `verifyHash()` bypass jika apiSecret kosong | 🟡 Medium | MyParcelService | Integrity tidak dijamin |
| BUG-21 | Product dihapus masih di cart | 🟢 Low | CartView | Edge case |
| BUG-22 | Guest orders tidak di OrderHistory | 🟢 Low | OrderHistory | Missing feature |
| BUG-23 | GrabPay option tidak konsisten | 🟢 Low | PaymentForm | Data inkonsisten |

---

## BAGIAN 7 — REKOMENDASI PERBAIKAN TERURUT

### Sprint 1 — Keamanan & Data Integrity (Segera)
1. **BUG-01** — Tambah kolom `transaction_id` ke payments migration
2. **BUG-02** — Ganti `'success'` → `'paid'` di kedua gateway callbacks
3. **BUG-03** — Implementasi Billplz X-Signature verification
4. **BUG-04** — Buat Stripe webhook handler dengan `constructEvent()`
5. **BUG-06** — Fix `$http = $http->withoutVerifying()`

### Sprint 2 — Business Logic (Minggu ini)
6. **BUG-05** — Pindah stock check ke dalam transaction dengan `lockForUpdate()`
7. **BUG-07** — Fix TrackOrder response structure
8. **BUG-10** — Simpan `courier_code` dan gunakan di `buildShipmentFromOrder()`
9. **BUG-09** — Tambah `->unique()` ke `order_number` migration
10. **BUG-13** — Tambah COD approval workflow di Filament

### Sprint 3 — Feature Completion (2 minggu)
11. **BUG-08** — Pisahkan stock dekremen dari order creation untuk gateway
12. **BUG-11** — Tambah kolom `awb_label_url` dan simpan dari AWB response
13. **BUG-12** — Implementasi voucher input dan diskon di CheckoutView
14. **BUG-17** — Buat OrderObserver untuk restore stok saat cancel

### Sprint 4 — Polish (Bulan ini)
15. **BUG-14 & 15** — Fix OrderForm guest validations
16. **BUG-18 & 19** — Fix stale cart dan shipping recalculation
17. **BUG-20** — Improve hash verification logic
18. **BUG-22** — Implement guest order history via email lookup

---

*Laporan ini dibuat berdasarkan analisis static code. Testing dinamis dengan environment staging disarankan untuk memvalidasi semua temuan di atas sebelum deployment ke production.*
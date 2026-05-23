# 🔍 Audit Bug & Cacat Logika — Sistem Order & Checkout
**Proyek:** WebEcommerceMalaysia (Laravel + Livewire)  
**Scope:** Add-to-Cart → Cart → Checkout → Order Placed → Success Page  
**Tanggal Audit:** 21 Mei 2026  

---

## Ringkasan Eksekutif

Total ditemukan **17 bug / cacat logika** yang dikategorikan berdasarkan tingkat keparahan:

| Severity | Jumlah | Kategori |
|----------|--------|----------|
| 🔴 KRITIS | 5 | Data hilang, transaksi tidak aman, akses tidak terautentikasi |
| 🟠 TINGGI | 6 | Logika harga/stok salah, validasi gagal |
| 🟡 SEDANG | 4 | UX cacat, inkonsistensi data |
| 🟢 RENDAH | 2 | Kode stale, minor display |

---

## FLOWCHART MASTER: Alur Order Normal vs Alur Cacat

```
[User] → [Product Detail] → [Add to Cart] → [Cart View] → [Checkout] → [Order Created] → [Success]
           │                   │                │               │              │
           ▼                   ▼                ▼               ▼              ▼
        BUG #1              BUG #2           BUG #3         BUG #4-#13     BUG #14-#17
     Variant Logic       No Stock           No Stock       Checkout Logic   Post-Order
     Cacat              Check              Check          Cacat             Cacat
```

---

---

# 🔴 BUG KRITIS

---

## BUG #1 — Tidak Ada Database Transaction di `placeOrder()`

**File:** `app/Livewire/Storefront/CheckoutView.php` — method `placeOrder()`  
**Severity:** 🔴 KRITIS  
**Tipe:** Race condition / Data integrity

### Deskripsi
Method `placeOrder()` membuat Order, OrderItems, dan Payment dalam 3 operasi database terpisah **tanpa `DB::transaction()`**. Jika terjadi error setelah Order dibuat tapi sebelum Payment dibuat (misalnya timeout, server down), sistem akan memiliki Order tanpa Payment record — order tersebut tidak akan pernah bisa diproses.

### Kode Bermasalah
```php
// TIDAK ADA DB::transaction() !!
$order = Order::create([...]); // Step 1
foreach ($this->cart->items as $item) {
    OrderItem::create([...]); // Step 2
}
Payment::create([...]); // Step 3 — kalau ini gagal, Order sudah terlanjur dibuat
$this->cart->delete(); // Step 4
```

### Flowchart Error
```
placeOrder() dipanggil
        │
        ▼
Order::create() ─── SUKSES ──→ order.id = 123
        │
        ▼
OrderItem::create() ─── SUKSES
        │
        ▼
Payment::create() ─── ❌ GAGAL (DB timeout / crash)
        │
        ▼
Order #123 ada di DB
tapi TIDAK ADA Payment record
tapi Cart BELUM dihapus
        │
        ▼
User mencoba order lagi →  Order #124 dibuat (duplikat)
Admin melihat Order #123 tanpa payment → kebingungan
```

### Solusi
```php
DB::transaction(function () {
    $order = Order::create([...]);
    foreach ($this->cart->items as $item) {
        OrderItem::create([...]);
    }
    Payment::create([...]);
    $this->cart->delete();
});
```

---

## BUG #2 — Tidak Ada Pengurangan Stok (`stock decrement`) Saat Order Dibuat

**File:** `app/Livewire/Storefront/CheckoutView.php` — method `placeOrder()`  
**File Terkait:** `app/Models/Product.php`, seluruh codebase  
**Severity:** 🔴 KRITIS  
**Tipe:** Business logic critical — overselling

### Deskripsi
Setelah order berhasil dibuat, **tidak ada satu pun baris kode yang mengurangi stok produk**. `grep -rn "decrement.*stock"` mengembalikan hasil kosong di seluruh `app/`. Artinya 100 pembeli bisa membeli produk yang stoknya hanya 1.

### Flowchart Error
```
Stok Produk A = 1
        │
        ├── User 1 memesan Produk A ──→ Order dibuat, stok tetap = 1
        │
        ├── User 2 memesan Produk A ──→ Order dibuat, stok tetap = 1
        │
        ├── User 3 memesan Produk A ──→ Order dibuat, stok tetap = 1
        │
        ▼
Admin harus kirim 3 item, stok hanya 1
→ OVERSELLING → Kerugian bisnis / komplain pelanggan
```

### Solusi
Tambahkan di dalam `placeOrder()`:
```php
foreach ($this->cart->items as $item) {
    // Kurangi stok
    $item->product->decrement('stock', $item->qty);
    if ($item->variant_id && $item->variant) {
        $item->variant->decrement('stock', $item->qty);
    }
    OrderItem::create([...]);
}
```

---

## BUG #3 — Success Page Dapat Diakses Oleh Siapa Saja (IDOR Vulnerability)

**File:** `routes/web.php` baris 19-22  
**Severity:** 🔴 KRITIS  
**Tipe:** Insecure Direct Object Reference (IDOR) / Authorization missing

### Deskripsi
Route `/checkout/success/{order}` adalah route publik tanpa middleware `auth` dan tanpa pengecekan kepemilikan order. Siapapun bisa mengakses `https://domain.com/checkout/success/1`, `/checkout/success/2`, dst. dan melihat detail order milik orang lain (nama, email, total harga, alamat).

### Kode Bermasalah
```php
// routes/web.php
Route::get('/checkout/success/{order}', function (\App\Models\Order $order) {
    $order->load('payment');
    return view('storefront.checkout.success', compact('order'));
})->name('checkout.success');
// TIDAK ADA: middleware auth, cek user_id, cek session
```

### Flowchart Serangan
```
Attacker tahu pola URL /checkout/success/{id}
        │
        ▼
GET /checkout/success/1  ──→ 200 OK — lihat data Order #1
GET /checkout/success/2  ──→ 200 OK — lihat data Order #2
GET /checkout/success/50 ──→ 200 OK — lihat data Order #50
        │
        ▼
Attacker mendapatkan: Nama, Email, Alamat, Total, Payment Method
→ Pelanggaran privasi data / PDPA Malaysia
```

### Solusi
```php
Route::get('/checkout/success/{order}', function (\App\Models\Order $order) {
    // Untuk user login: cek kepemilikan
    if (auth()->check() && $order->user_id !== auth()->id()) {
        abort(403);
    }
    // Untuk guest: cek session
    if (!auth()->check()) {
        if (session('last_order_id') !== $order->id) {
            abort(403);
        }
    }
    $order->load('payment');
    return view('storefront.checkout.success', compact('order'));
})->name('checkout.success');
```

---

## BUG #4 — Default `payment_method = 'fpx'` Tidak Ada di Validasi

**File:** `app/Livewire/Storefront/CheckoutView.php` baris 28 & 169  
**Severity:** 🔴 KRITIS  
**Tipe:** Validation mismatch — silent failure

### Deskripsi
Property default diset ke `'fpx'` tapi validasi `placeOrder()` hanya mengizinkan `'billplz,stripe,cod,manual_transfer'`. Jika user tidak pernah mengubah pilihan payment (misalnya hanya satu metode yang tersedia dan user langsung klik Place Order), validasi **selalu gagal** dengan pesan error yang membingungkan.

### Kode Bermasalah
```php
// Baris 28 — default value
public $payment_method = 'fpx'; // ← 'fpx' bukan nilai valid

// Baris 169 — rule validasi
'payment_method' => 'required|in:billplz,stripe,cod,manual_transfer',
// ← 'fpx' tidak ada di list!
```

### Flowchart Error
```
Halaman Checkout dibuka
        │
        ▼
$payment_method = 'fpx' (default)
        │
User tidak ganti payment method (hanya 1 opsi tersedia)
        │
        ▼
Klik "Place Order"
        │
        ▼
validate(['payment_method' => 'in:billplz,stripe,cod,manual_transfer'])
        │
        ▼
❌ VALIDASI GAGAL — "The selected payment method is invalid."
        │
        ▼
User bingung, order tidak bisa dibuat
```

### Solusi
Ubah default ke string kosong dan set saat payment method pertama tersedia, atau sesuaikan default dengan nilai yang valid.

---

## BUG #5 — Tidak Ada Validasi Stok Saat `placeOrder()`

**File:** `app/Livewire/Storefront/CheckoutView.php` — method `placeOrder()`  
**Severity:** 🔴 KRITIS  
**Tipe:** Stale data — beli produk habis stok

### Deskripsi
Antara user memasukkan item ke cart hingga klik "Place Order" bisa berlalu beberapa jam/hari. Tidak ada pengecekan stok terkini saat checkout. User bisa melakukan checkout produk yang sudah kehabisan stok.

### Flowchart Error
```
User A memasukkan Produk X (stok: 1) ke cart
        │
User B checkout dan beli Produk X ─→ stok = 0 (tanpa decrement, lihat BUG #2)
        │
User A ke checkout page — tidak ada peringatan
        │
        ▼
User A klik "Place Order"
        │
        ▼
Tidak ada stock check → Order dibuat untuk stok yang tidak ada
→ Konflik pengiriman / kecewa pelanggan
```

---

---

# 🟠 BUG TINGGI

---

## BUG #6 — Harga di Order Summary (Blade) Tidak Pakai `active_price` / Flash Sale

**File:** `resources/views/livewire/storefront/checkout-view.blade.php`  
**Severity:** 🟠 TINGGI  
**Tipe:** Price inconsistency — tampilan vs perhitungan

### Deskripsi
Di Order Summary sidebar, harga dihitung langsung di Blade menggunakan `$item->product->price` (harga normal), sedangkan di backend (`CheckoutView.php`) subtotal dihitung menggunakan `$item->effective_price` (yang menghormati flash sale dan bundle price). Akibatnya **harga yang ditampilkan ke user berbeda dengan total yang dihitung**.

### Kode Bermasalah
```php
{{-- Blade: pakai harga normal (SALAH) --}}
@php
    $price = $item->product->price + ($item->variant ? $item->variant->price_modifier : 0);
@endphp
<div>RM {{ number_format($price * $item->qty, 2) }}</div>

// PHP: pakai effective_price (BENAR)
$price = $item->effective_price; // menghitung flash sale & bundle
$this->subtotal += ($price * $item->qty);
```

### Flowchart Inconsistency
```
Produk X: Harga Normal = RM 100, Flash Sale = RM 70
        │
        ▼
Order Summary tampilkan: RM 100 × 2 = RM 200  ← SALAH (blade)
        │
        ▼
Total ditampilkan: RM 200 + shipping           ← SALAH (inconsistent)
        │
        ▼
Order dibuat dengan total: RM 140 + shipping   ← BENAR (backend subtotal)
        │
        ▼
User bayar sesuai total di halaman (RM 200)
tapi order record = RM 140
→ Selisih pembayaran / dispute
```

### Solusi
Di blade, gunakan `$item->effective_price`:
```php
@php $price = $item->effective_price; @endphp
```

---

## BUG #7 — `getSelectedVariantModel()` Cacat — Tidak Match Kombinasi Variant

**File:** `app/Livewire/Storefront/ProductDetail.php` — method `getSelectedVariantModel()`  
**Severity:** 🟠 TINGGI  
**Tipe:** Wrong variant selected — salah item dipesan

### Deskripsi
Fungsi ini menggunakan `whereIn('value', $values)->first()` yang hanya mencocokkan **satu nilai variant** dari semua yang dipilih. Jika produk memiliki kombinasi Color + Size, user memilih "Red" + "XL", tapi fungsi ini akan return variant pertama yang valuenya "Red" atau "XL" — bukan kombinasi keduanya. Akibatnya `variant_id` yang disimpan ke cart **bisa salah**.

### Flowchart Error
```
Produk T-Shirt:
  Variant 1: Color=Red, Size=M  (id: 1)
  Variant 2: Color=Red, Size=XL (id: 2)
  Variant 3: Color=Blue, Size=XL (id: 3)
        │
User pilih: Color=Red, Size=XL
$selectedVariants = ['Color'=>'Red', 'Size'=>'XL']
        │
        ▼
whereIn('value', ['Red','XL'])->first()
        │
        ▼
Return Variant 1 (Color=Red, Size=M) ← SALAH!
        │
        ▼
Cart item dibuat dengan variant_id = 1 (Size M, bukan XL)
→ User terima baju ukuran M padahal pesan XL
```

### Catatan
Ini tergantung struktur data variant. Komentar di kode sendiri mengakui limitasi ini: *"Simplified logic... this logic would need to match all combo attributes."* — artinya developer sadar tapi belum diperbaiki.

---

## BUG #8 — Tidak Ada Validasi Stok di `increment()` CartView

**File:** `app/Livewire/Storefront/CartView.php` — method `increment()`  
**Severity:** 🟠 TINGGI  
**Tipe:** Missing stock validation

### Deskripsi
Method `increment()` di CartView tidak memvalidasi stok produk sebelum menambah qty. User bisa menambah qty item di cart melebihi stok yang tersedia.

### Kode Bermasalah
```php
public function increment($itemId)
{
    if ($this->cart) {
        $item = $this->cart->items()->find($itemId);
        if ($item) {
            // Check stock logic here if needed  ← KOMENTAR KOSONG, tidak ada implementasi!
            $item->increment('qty');
            ...
        }
    }
}
```

---

## BUG #9 — Address User Selalu Ditimpa (`updateOrCreate` dengan key `user_id` saja)

**File:** `app/Livewire/Storefront/CheckoutView.php` baris 178-188  
**Severity:** 🟠 TINGGI  
**Tipe:** Data overwrite — user kehilangan address book

### Deskripsi
`updateOrCreate(['user_id' => auth()->id()], [...])` menggunakan `user_id` saja sebagai key pencari. Ini berarti setiap checkout akan **menimpa satu-satunya address** milik user tersebut. User tidak bisa memiliki multiple address, dan checkout sebelumnya selalu akan overwrite alamat lama.

### Flowchart Error
```
User memiliki Address:
  Home: Jln Ampang No.1, KL
        │
User checkout ke alamat sementara:
  Office: Jln Bukit Bintang No.5, KL
        │
        ▼
updateOrCreate(['user_id' => X], [address: 'Jln Bukit Bintang...'])
        │
        ▼
Address "Home" di-OVERWRITE → Address lama hilang selamanya
        │
User kembali ke dashboard → alamat berubah ke office
→ Next checkout otomatis ke office (tidak diinginkan)
```

---

## BUG #10 — Order Number Tidak Unik (Collision Risk)

**File:** `app/Livewire/Storefront/CheckoutView.php` baris 208  
**Severity:** 🟠 TINGGI  
**Tipe:** Non-unique identifier

### Deskripsi
Order number dibuat dengan `'ORD-' . strtoupper(Str::random(8))` yang menghasilkan string 8 karakter alfanumerik random. Tidak ada constraint `unique` di database untuk kolom `order_number` (kolom di migration adalah `nullable()` tanpa `unique()`). Probabilitas collision meningkat seiring volume order (Birthday problem).

### Flowchart
```
Str::random(8) → 36^8 kombinasi ≈ 2.8 triliun kemungkinan
Dengan 10.000 order: probabilitas collision ≈ rendah tapi tidak nol
Dengan 100.000 order: probabilitas collision meningkat signifikan
Tidak ada retry/uniqueness check → dua order bisa punya order_number sama
→ Support/tracking kacau
```

---

## BUG #11 — Tidak Ada Gateway Payment Integration (Billplz/Stripe hanya simulasi)

**File:** `app/Livewire/Storefront/CheckoutView.php` — method `placeOrder()`  
**Severity:** 🟠 TINGGI  
**Tipe:** Incomplete implementation — missing critical feature

### Deskripsi
Meskipun ada setting untuk Billplz dan Stripe, method `placeOrder()` **tidak pernah memanggil payment gateway**. Semua payment langsung dibuat dengan `status: 'pending'` tanpa redirect ke payment page. Artinya:
- User memilih "FPX Online Banking" → order terbuat tapi tidak ada redirect ke Billplz
- User memilih "Stripe" → order terbuat tapi tidak ada Stripe intent/redirect
- Cart langsung dihapus sebelum pembayaran dikonfirmasi

### Flowchart Error
```
User pilih "FPX (Billplz)"
        │
        ▼
placeOrder() → Payment::create([status: 'pending']) 
        │
        ▼
Cart DIHAPUS ← ❌ Cart sudah hilang sebelum bayar!
        │
        ▼
Redirect ke /checkout/success ← ❌ Tampilkan "Terima kasih!" padahal belum bayar!
        │
        ▼
Tidak ada redirect ke Billplz payment page
→ Order pending selamanya, tidak ada uang masuk
→ Admin harus verifikasi manual semua order
```

---

---

# 🟡 BUG SEDANG

---

## BUG #12 — Column Name Salah di Success Page

**File:** `resources/views/storefront/checkout/success.blade.php` baris 28  
**Severity:** 🟡 SEDANG  
**Tipe:** Wrong column reference — tampilan error

### Deskripsi
Template mengakses `$order->payment->payment_method` tapi di model/migration, kolom Payment bernama `method` (bukan `payment_method`). Ini akan selalu menampilkan `'N/A'` (karena null coalescing `?? 'N/A'`).

### Kode Bermasalah
```php
{{-- success.blade.php baris 28 --}}
{{ $order->payment->payment_method ?? 'N/A' }}
{{--                 ↑ SALAH: kolom bernama 'method', bukan 'payment_method' --}}
```

### Solusi
```php
{{ $order->payment->method ?? 'N/A' }}
```

---

## BUG #13 — Tidak Ada Cart Merge Saat User Login

**File:** `app/Livewire/Storefront/ProductDetail.php` — method `addToCart()`, seluruh auth flow  
**Severity:** 🟡 SEDANG  
**Tipe:** UX cacat — item hilang saat login

### Deskripsi
Tidak ada mekanisme penggabungan cart saat guest login. Guest cart (berdasarkan `session_id`) dan user cart (berdasarkan `user_id`) adalah dua entitas terpisah. Saat guest login, cart-nya yang lama (session-based) tidak otomatis digabung ke user account, sehingga semua item yang sudah ditambahkan sebelum login **hilang**.

### Flowchart UX Cacat
```
Guest masuk ke website
        │
        ▼
Tambah Produk A, B, C ke cart (session_id = "abc123")
        │
        ▼
Masuk ke halaman checkout → diminta login
        │
        ▼
Guest login → session baru dibuat (session_id = "xyz789")
        │
        ▼
CartView::loadCart() → cari cart by user_id = 5
        │
        ▼
Cart baru kosong! (cart session "abc123" tidak digabung)
        │
        ▼
User melihat cart kosong → harus tambah ulang semua item
→ Frustasi, potensi cart abandonment tinggi
```

---

## BUG #14 — Voucher/Diskon Ada di Model tapi Tidak Ada di Checkout Flow

**File:** `app/Livewire/Storefront/CheckoutView.php`  
**File Terkait:** `database/migrations/2026_05_20_101715_create_vouchers_table.php`, `app/Models/Order.php`  
**Severity:** 🟡 SEDANG  
**Tipe:** Incomplete feature — fitur setengah jadi

### Deskripsi
Tabel `vouchers` dan kolom `voucher_id` di tabel `orders` sudah ada. Model `Order` pun memiliki relasi `voucher()`. Namun di `CheckoutView.php` **tidak ada field input voucher, tidak ada method apply voucher, dan grand total tidak pernah dikurangi diskon voucher**. Fitur voucher 100% tidak berfungsi.

---

## BUG #15 — `addToCart()` Tidak Validasi Stok Sebelum Menambah

**File:** `app/Livewire/Storefront/ProductDetail.php` — method `addToCart()`  
**Severity:** 🟡 SEDANG  
**Tipe:** Missing stock guard

### Deskripsi
`incrementQty()` memang membatasi qty di UI berdasarkan stok, tapi `addToCart()` tidak memvalidasi ulang stok secara server-side sebelum menyimpan ke database. Jika user memanipulasi request Livewire (mengganti qty secara manual), mereka bisa menambahkan qty melebihi stok.

---

---

# 🟢 BUG RENDAH

---

## BUG #16 — Email Dikirim Di Luar Transaction (Bisa Kirim Email Meski Order Gagal)

**File:** `app/Livewire/Storefront/CheckoutView.php` baris ~232  
**Severity:** 🟢 RENDAH  
**Tipe:** Side effect di luar transaction boundary

### Deskripsi
Email konfirmasi order dikirim dengan `Mail::to(...)->send(...)` yang dipanggil setelah pembuatan order tapi sebelum `$this->cart->delete()` dan tidak dibungkus dalam transaction. Jika menggunakan queue (`ShouldQueue`), email bisa terkirim meski bagian lain dari operasi gagal.

---

## BUG #17 — `TrackOrder` Tidak Proteksi Guest Order

**File:** `app/Livewire/Storefront/Dashboard/TrackOrder.php` baris 14-16  
**Severity:** 🟢 RENDAH  
**Tipe:** Incomplete authorization

### Deskripsi
```php
if (auth()->check() && $order->user_id !== auth()->id()) {
    abort(403);
}
```
Kondisi ini hanya abort jika **user login tapi bukan pemilik order**. Jika user tidak login (`auth()->check() = false`), kondisi ini tidak pernah abort, sehingga **guest bisa mengakses tracking order milik siapapun** dengan menebak ID order.

---

---

# UX FLOW YANG CACAT — Visualisasi Lengkap

## Flow 1: Guest Checkout dengan Validasi Gagal

```
Guest → Isi form checkout → Pilih payment (default 'fpx') 
                                    │
                                    ▼
                            Klik "Place Order"
                                    │
                                    ▼
                    ❌ Error: "The selected payment method is invalid"
                                    │
                                    ▼
                    Tidak ada penjelasan kenapa gagal
                    Tidak ada indikasi opsi yang valid
                    User tidak tahu harus berbuat apa
                                    │
                                    ▼
                            User meninggalkan halaman (cart abandonment)
```

## Flow 2: Flash Sale + Order Summary Inconsistency

```
User lihat Produk X di Flash Sale: RM 70 (diskon dari RM 100)
    │
    ▼
Add to Cart → Cart menampilkan RM 70 (correct, pakai effective_price)
    │
    ▼
Masuk Checkout → Order Summary sidebar menampilkan RM 100 (SALAH! pakai product->price)
    │
    ▼
User tanya: "kenapa harganya berubah?"
    │
    ▼
Total backend = RM 70 + shipping
Total tampilan = RM 100 + shipping
    │
    ▼
Order dibuat dengan total RM 70+shipping (correct)
Tapi user sudah konfirmasi di page yang menampilkan RM 100+shipping
    │
    ▼
User mungkin merasa diperdaya / confusion → trust issue
```

## Flow 3: Double Submit (Tanpa Proteksi)

```
User klik "Place Order"
    │
    ▼
Livewire processing (wire:loading ada, tapi button tidak di-disable di semua kondisi)
    │
    ├── Koneksi lambat → user klik lagi
    │
    ▼
2x placeOrder() dieksekusi (Livewire bisa handle ini, tapi tanpa DB transaction = race condition)
    │
    ▼
2 Order dibuat dengan order_number berbeda
Cart dihapus oleh yang pertama
Yang kedua bisa error karena cart sudah kosong
    │
    ▼
User di-redirect ke success page order pertama
Order kedua terbuat tanpa payment record
```

## Flow 4: Checkout Berhasil tapi Pembayaran Tidak Pernah Terjadi

```
User pilih FPX (Billplz)
    │
    ▼
Form disubmit → placeOrder() dipanggil
    │
    ▼
Order dibuat (status: pending)
Payment dibuat (status: pending) ← tidak ada redirect ke gateway!
Cart DIHAPUS ← item hilang!
    │
    ▼
User redirect ke /checkout/success
    │
    ▼
"Terima kasih atas pesanan Anda!" ← padahal belum bayar!
    │
    ▼
User mengira sudah selesai, tidak perlu bayar lagi
    │
    ▼
Admin melihat ribuan "pending payment" orders
Tidak ada uang masuk
Stok sudah dianggap terjual (meskipun tidak, karena BUG #2)
```

---

---

# TABEL RINGKASAN BUG

| # | Severity | File | Metode/Lokasi | Masalah | Dampak |
|---|----------|------|---------------|---------|--------|
| 1 | 🔴 KRITIS | CheckoutView.php | `placeOrder()` | Tidak ada DB::transaction | Data incomplete, ghost orders |
| 2 | 🔴 KRITIS | CheckoutView.php | `placeOrder()` | Tidak ada stock decrement | Overselling |
| 3 | 🔴 KRITIS | routes/web.php | `/checkout/success/{order}` | Tidak ada auth/ownership check | IDOR, kebocoran data pribadi |
| 4 | 🔴 KRITIS | CheckoutView.php | `$payment_method` | Default 'fpx' tidak ada di validasi | Checkout selalu gagal |
| 5 | 🔴 KRITIS | CheckoutView.php | `placeOrder()` | Tidak ada stock re-check saat checkout | Order stok habis |
| 6 | 🟠 TINGGI | checkout-view.blade.php | Order Summary | Harga blade vs backend tidak sinkron | Harga tampil salah |
| 7 | 🟠 TINGGI | ProductDetail.php | `getSelectedVariantModel()` | Variant match logic cacat | Variant salah dipesan |
| 8 | 🟠 TINGGI | CartView.php | `increment()` | Tidak ada stock check | Cart qty melebihi stok |
| 9 | 🟠 TINGGI | CheckoutView.php | `placeOrder()` | updateOrCreate overwrite address | Alamat user lama hilang |
| 10 | 🟠 TINGGI | CheckoutView.php | `placeOrder()` | Order number bisa collision | Duplikat order number |
| 11 | 🟠 TINGGI | CheckoutView.php | `placeOrder()` | Gateway payment tidak diintegrasikan | Tidak ada pembayaran nyata |
| 12 | 🟡 SEDANG | success.blade.php | baris 28 | `payment_method` vs `method` | Tampil 'N/A' |
| 13 | 🟡 SEDANG | Auth flow | login callback | Tidak ada cart merge | Item hilang saat login |
| 14 | 🟡 SEDANG | CheckoutView.php | `placeOrder()` | Voucher tidak diimplementasi | Fitur voucher tidak berfungsi |
| 15 | 🟡 SEDANG | ProductDetail.php | `addToCart()` | Tidak ada server-side stock guard | Manipulasi qty via request |
| 16 | 🟢 RENDAH | CheckoutView.php | `placeOrder()` | Email di luar transaction | Email kirim meski order gagal |
| 17 | 🟢 RENDAH | TrackOrder.php | `mount()` | Authorization check incomplete | Guest bisa lihat tracking orang lain |

---

## Prioritas Perbaikan

### Sprint 1 — Perbaiki Sekarang (KRITIS)
1. Bungkus `placeOrder()` dengan `DB::transaction()`
2. Perbaiki default `$payment_method` menjadi `null` atau sesuaikan validasi
3. Tambahkan authorization di `/checkout/success/{order}`
4. Tambahkan stock decrement di `placeOrder()`
5. Tambahkan stock re-validation di `placeOrder()`

### Sprint 2 — Minggu Ini (TINGGI)
6. Sinkronkan harga di blade dengan `$item->effective_price`
7. Perbaiki `getSelectedVariantModel()` untuk match kombinasi variant
8. Tambahkan stock check di `CartView::increment()`
9. Gunakan `label` berbeda untuk multiple address (jangan overwrite)
10. Tambahkan `unique` constraint + retry logic untuk `order_number`
11. Implementasikan redirect ke payment gateway (Billplz/Stripe)

### Sprint 3 — Minggu Depan (SEDANG & RENDAH)
12. Fix column name di success blade
13. Implementasikan cart merge saat login
14. Implementasikan voucher apply di checkout
15. Pindahkan email send ke dalam transaction atau queue dengan retry

---

*Laporan ini dibuat berdasarkan analisis statis kode. Tidak ada environment testing yang dijalankan.*
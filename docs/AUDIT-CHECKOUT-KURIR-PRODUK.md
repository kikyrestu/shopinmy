# 🔍 FULL AUDIT REPORT: Checkout, Kurir & Produk
## WebEcommerceMalaysia — Bug, Error & Cacat Logika

> **Tanggal Audit:** 2026-05-22  
> **Scope:** Modul Checkout, Kurir (MyParcel), dan Produk  
> **Layer:** Backend (Laravel/Livewire), Frontend (Blade/Storefront), Admin (Filament)

---

## 📑 DAFTAR ISI

1. [Executive Summary](#1-executive-summary)
2. [CRITICAL BUGS — Checkout Flow](#2-critical-bugs--checkout-flow)
3. [CRITICAL BUGS — Kurir / Shipping](#3-critical-bugs--kurir--shipping)
4. [CRITICAL BUGS — Produk](#4-critical-bugs--produk)
5. [MEDIUM BUGS — Logic Defects](#5-medium-bugs--logic-defects)
6. [LOW / UX Defects](#6-low--ux-defects)
7. [Filament Admin Panel Defects](#7-filament-admin-panel-defects)
8. [Flowchart: Checkout Bug Map](#8-flowchart-checkout-bug-map)
9. [Flowchart: Stock Decrement Race Condition](#9-flowchart-stock-decrement-race-condition)
10. [Flowchart: Payment Callback Double-Deduction](#10-flowchart-payment-callback-double-deduction)
11. [Flowchart: Voucher Abuse Flow](#11-flowchart-voucher-abuse-flow)
12. [Flowchart: Cart Price Mismatch](#12-flowchart-cart-price-mismatch)
13. [Flowchart: Guest Checkout UX Issues](#13-flowchart-guest-checkout-ux-issues)
14. [Ringkasan Solusi](#14-ringkasan-solusi)

---

## 1. Executive Summary

| Severity | Count |
|----------|-------|
| 🔴 CRITICAL | 8 |
| 🟠 MEDIUM | 9 |
| 🟡 LOW / UX | 7 |
| **TOTAL** | **24** |

Bug terberat ada di **stock double-decrement** saat payment callback, **voucher discount tidak masuk ke grand total Payment record**, **race condition pada checkout concurrent**, dan **cart view menampilkan harga yang berbeda dari checkout**.

---

## 2. CRITICAL BUGS — Checkout Flow

### BUG-C01: 🔴 Payment Amount Mismatch (Discount Tidak Dihitung di Payment Record)

**File:** `app/Livewire/Storefront/CheckoutView.php:389-395`

```
Payment::create([
    ...
    'amount' => $grandTotal,  // ← BUG: $grandTotal BELUM dikurangi $this->discountAmount
    ...
]);
```

**Masalah:** Order `total` = `$grandTotal - $discountAmount` (line 350), tapi Payment `amount` = `$grandTotal` (tanpa discount). Akibatnya:
- Customer **dicharge lebih besar** dari yang seharusnya di Billplz/Stripe
- Payment amount ≠ Order total → laporan keuangan tidak konsisten

**Solusi:**
```php
'amount' => $grandTotal - $this->discountAmount,
```

---

### BUG-C02: 🔴 Stock Double-Decrement via Payment Callbacks

**File:** `app/Http/Controllers/PaymentController.php:154-165`, `217-256`, `app/Http/Controllers/StripeWebhookController.php:38-49`

**Masalah:** Stock didecrement di **3 tempat terpisah** yang bisa semua terpanggil untuk satu order:

1. `billplzCallback()` → decrement stock (line 159-164)
2. `stripeCallback()` → decrement stock (line 199-204)
3. `billplzWebhook()` → decrement stock (line 245-249)
4. `StripeWebhookController::handle()` → decrement stock (line 44-49)

**Flow Error:**
```
Customer bayar via Billplz
  → Browser redirect ke billplzCallback() → stock -1 ✓
  → Billplz server kirim POST ke billplzWebhook() → stock -1 LAGI ❌
  
Hasil: Stock dikurangi 2x untuk 1 order!
```

Guard `$order->payment->status !== 'paid'` **tidak cukup** karena callback redirect dan webhook bisa terjadi hampir bersamaan (race condition).

**Solusi:**
```php
// Gunakan DB::transaction + lockForUpdate pada payment record
$payment = Payment::lockForUpdate()->find($order->payment->id);
if ($payment->status === 'paid') return; // already processed
$payment->update(['status' => 'paid']);
// ... decrement stock
```

---

### BUG-C03: 🔴 Stock Decrement Tanpa Null-Check pada Product Stock

**File:** `PaymentController.php:159`, `StripeWebhookController.php:44`

```php
$item->product->decrement('stock', $item->qty);
```

**Masalah:** Di `CheckoutView.php:378` ada null-check (`if ($item->product->stock !== null)`), tapi di **semua payment callback** TIDAK ada null-check. Jika product stock = `null` (unlimited stock mode), `decrement()` akan meng-set stock ke nilai negatif.

**Solusi:** Tambahkan null-check konsisten:
```php
if ($item->product->stock !== null) {
    $item->product->decrement('stock', $item->qty);
}
```

---

### BUG-C04: 🔴 Order Number Collision — No Guarantee of Uniqueness

**File:** `app/Livewire/Storefront/CheckoutView.php:329-336`

```php
$orderNumber = null;
$maxRetries = 3;
for ($i = 0; $i < $maxRetries; $i++) {
    $orderNumber = 'ORD-' . strtoupper(Str::random(8));
    if (!Order::where('order_number', $orderNumber)->exists()) {
        break;
    }
}
// ← Jika 3x retry gagal, $orderNumber tetap dipakai (DUPLICATE!)
```

**Masalah:** Setelah 3 retry, order tetap dibuat dengan order_number yang mungkin duplikat. Database memang ada unique constraint tapi akan throw exception yang tidak di-handle gracefully.

**Solusi:**
```php
// Setelah loop, pastikan break terjadi karena unique
if (Order::where('order_number', $orderNumber)->exists()) {
    throw new \Exception('Unable to generate unique order number. Please try again.');
}
```

---

### BUG-C05: 🔴 Voucher Race Condition — Double Usage

**File:** `app/Livewire/Storefront/CheckoutView.php:208-253, 359-361`

**Masalah:** Voucher di-validate di `applyVoucher()` (di luar transaction), tapi `used_count` baru di-increment di dalam `placeOrder()` transaction (line 360). Dua customer bisa apply voucher yang sama secara bersamaan, keduanya lolos validasi `usage_limit`, dan keduanya berhasil increment → **voucher dipakai melebihi limit**.

Juga: `applyVoucher()` tidak di-revalidasi di dalam `placeOrder()`. Voucher bisa expire antara apply dan place.

**Solusi:**
```php
// Di dalam DB::transaction di placeOrder():
if ($this->voucherId) {
    $voucher = Voucher::lockForUpdate()->find($this->voucherId);
    if (!$voucher || !$voucher->isValid() || ...) {
        throw new \Exception('Voucher is no longer valid.');
    }
    $voucher->increment('used_count');
}
```

---

### BUG-C06: 🔴 Guest Checkout Route Tidak Dilindungi — OrderHistory Leaks Data

**File:** `app/Livewire/Storefront/Dashboard/OrderHistory.php:15-16`

```php
$orders = Order::where('user_id', auth()->id())
    ->orWhere('guest_email', auth()->user()->email)
    ...
```

**Masalah:** `orWhere` tanpa grouping menyebabkan query menjadi:
```sql
WHERE user_id = 5 OR guest_email = 'user@example.com'
```
Ini mengembalikan **SEMUA** guest orders dari siapapun yang kebetulan pakai email yang sama, TANPA scope `user_id`. Jika seseorang mendaftar dengan email yang sama dengan guest order orang lain, mereka bisa lihat order tersebut.

**Solusi:**
```php
$orders = Order::where(function($q) {
    $q->where('user_id', auth()->id())
      ->orWhere(function($q2) {
          $q2->whereNull('user_id')
             ->where('guest_email', auth()->user()->email);
      });
})
```

---

### BUG-C07: 🔴 Checkout Tidak Auth-Guard — Semua Orang Bisa Order Tanpa Login

**File:** `routes/web.php:18`

```php
Route::get('/checkout', CheckoutView::class)->name('checkout.index');
```

**Masalah:** Route checkout tidak ada middleware `auth`. Ini memang intentional untuk guest checkout, **TAPI**:
- Di `placeOrder()` line 414: `session()->put('last_order_id', $order->id)` — hanya protect 1 order terakhir
- Jika guest buat 2 order, order pertama tidak bisa diakses lagi
- Payment process route (line 30) juga pakai session check — rawan hilang

Ini bukan "bug" sepenuhnya tapi merupakan **security gap** yang signifikan.

**Solusi:** Pertimbangkan menyimpan array of guest order IDs di session, atau force login sebelum checkout.

---

### BUG-C08: 🔴 Billplz Callback Menggunakan GET tanpa CSRF Tapi Tanpa Signature Verification yang Ketat

**File:** `routes/web.php:31`, `PaymentController.php:118-172`

```php
Route::get('/payment/callback/billplz', ...)->name('payment.callback.billplz');
```

**Masalah:** Billplz callback pakai GET request, parameter `order` dikirim via query string. Attacker bisa craft URL:
```
/payment/callback/billplz?order=123&billplz[id]=fake&billplz[state]=paid
```

X-Signature check di line 132 **hanya dilakukan jika** `$xSignatureKey` di-setting. Jika admin belum setting x_signature key, SEMUA requests akan lolos → attacker bisa mark order sebagai "paid" tanpa bayar.

**Solusi:** Jika `$xSignatureKey` kosong, REJECT semua callbacks:
```php
if (empty($xSignatureKey)) {
    abort(400, 'X-Signature key not configured');
}
```

---

## 3. CRITICAL BUGS — Kurir / Shipping

### BUG-K01: 🔴 calculateShipping() Dipanggil Tanpa Postcode pada City/State Update

**File:** `app/Livewire/Storefront/CheckoutView.php:106-107`

```php
public function updatedCity() { $this->calculateShipping(); }
public function updatedState() { $this->calculateShipping(); }
```

**Masalah:** Jika user mengisi city/state SEBELUM postcode, `calculateShipping()` dipanggil dengan `$this->postcode = null`. MyParcel API akan error atau return invalid rates. Tidak ada guard di `calculateShipping()` untuk memvalidasi postcode.

**Solusi:**
```php
public function updatedCity() { 
    if (strlen(trim($this->postcode ?? '')) >= 5) $this->calculateShipping(); 
}
public function updatedState() { 
    if (strlen(trim($this->postcode ?? '')) >= 5) $this->calculateShipping(); 
}
```

---

### BUG-K02: 🟠 isCalculatingShipping Flag Never Reset pada updateShipping()

**File:** `app/Livewire/Storefront/CheckoutView.php:116-120`

```php
public function updateShipping()
{
    $this->isCalculatingShipping = true;
    // The actual calculation is debounced in postcode update
}
```

**Masalah:** Method ini set `isCalculatingShipping = true` tapi **tidak pernah set false** dan tidak memanggil `calculateShipping()`. Jika dipanggil, UI akan stuck di "Calculating shipping rates..." selamanya.

**Solusi:** Remove method ini atau redirect ke `calculateShipping()`:
```php
public function updateShipping()
{
    if (strlen(trim($this->postcode ?? '')) >= 5) {
        $this->calculateShipping();
    }
}
```

---

### BUG-K03: 🟠 MyParcelService buildShipmentFromOrder() — send_date Selalu +1 Hari

**File:** `app/Services/MyParcelService.php:355`

```php
'send_date' => now()->addDay()->format('Y-m-d'),
```

**Masalah:** Jika admin generate AWB pada hari Jumat, send_date = Sabtu. Jika courier tidak pickup Sabtu, akan invalid. Tidak memperhitungkan weekend/holiday.

**Solusi:** Gunakan next business day atau biarkan admin pilih tanggal di Filament action form.

---

## 4. CRITICAL BUGS — Produk

### BUG-P01: 🔴 Cart View Blade Menampilkan Harga Berbeda dari Checkout

**File:** `resources/views/livewire/storefront/cart-view.blade.php:27-31`

```php
@php
    $price = $item->product->price;  // ← ORIGINAL price, BUKAN flash sale / active_price!
    if ($item->variant && $item->variant->price_modifier) {
        $price += $item->variant->price_modifier;
    }
@endphp
```

**Vs Checkout** (`CheckoutView.php:64`):
```php
$price = $item->effective_price;  // ← Memperhitungkan flash sale + bundle
```

**Masalah:** Cart page menampilkan **harga normal** sementara checkout menampilkan **harga flash sale/bundle**. Customer melihat harga berbeda antara cart dan checkout → **kebingungan dan hilangnya kepercayaan**.

**Flow Error:**
```
┌─────────────────────┐     ┌─────────────────────┐
│   CART PAGE          │     │   CHECKOUT PAGE      │
│   $item->product->   │     │   $item->effective_  │
│   price = RM 100     │  ≠  │   price = RM 70      │
│   (original price)   │     │   (flash sale price)  │
└─────────────────────┘     └─────────────────────┘
```

**Solusi:** Gunakan `$item->effective_price` di cart blade juga:
```php
@php
    $price = $item->effective_price;
@endphp
```

---

### BUG-P02: 🟠 Product::getActiveFlashSaleAttribute — N+1 Query di Loop

**File:** `app/Models/Product.php:74-81`

```php
public function getActiveFlashSaleAttribute()
{
    return $this->flashSales()
        ->where('is_active', true)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>=', now())
        ->first();
}
```

**Masalah:** Setiap kali `active_price` diakses (yang memanggil `is_on_flash_sale` → `active_flash_sale`), sebuah query baru dieksekusi. Di cart dengan 10 item, ini = **10+ extra queries** per page load. Bahkan lebih buruk di product listing.

**Solusi:** Eager load flash sales atau cache per request:
```php
public function getActiveFlashSaleAttribute()
{
    if ($this->relationLoaded('flashSales')) {
        return $this->flashSales
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }
    // fallback to query
    return $this->flashSales()...->first();
}
```

---

### BUG-P03: 🟠 ProductDetail addToCart() — Null Comparison Crash

**File:** `app/Livewire/Storefront/ProductDetail.php:124-125`

```php
$maxStock = $variant ? $variant->stock : $this->product->stock;
if ($this->qty > $maxStock) { ... }
```

**Masalah:** Jika product `stock = null` (unlimited mode, sesuai ProductForm helper text "Leave empty if using variants stock"), maka `$maxStock = null`. Operasi `$this->qty > null` selalu `false` di PHP (null coerced to 0), sehingga **qty 1 > 0 = true → "Insufficient stock" error** untuk unlimited stock product.

**Solusi:**
```php
$maxStock = $variant ? $variant->stock : $this->product->stock;
if ($maxStock !== null && $this->qty > $maxStock) {
    $this->dispatch('notify', message: __('Insufficient stock available.'));
    return;
}
```

---

### BUG-P04: 🟠 Variant Matching Logic Fragile — stripos False Positive

**File:** `app/Livewire/Storefront/ProductDetail.php:108-116`

```php
return $this->product->variants->first(function ($variant) use ($selectedValues) {
    foreach ($selectedValues as $val) {
        if (stripos($variant->value, $val) === false) {
            return false;
        }
    }
    return true;
});
```

**Masalah:** Matching dengan `stripos` menyebabkan false positive:
- Variant value = "**Red** Large" 
- Selected = ["**Red**", "Large"] → Match ✓
- Tapi juga: Variant value = "**Reddish** Large" → `stripos("Reddish Large", "Red")` ≠ false → **False Match** ❌

**Solusi:** Gunakan exact matching atau delimiter-based parsing:
```php
$variantValues = array_map('trim', explode(',', $variant->value));
// atau exact match per name-value pair
```

---

### BUG-P05: 🟠 CartView increment() — Stock Check Salah untuk Null Stock

**File:** `app/Livewire/Storefront/CartView.php:42-47`

```php
$productStock = $item->product->stock ?? 0;  // null → 0
$variantStock = $item->variant ? $item->variant->stock : null;
$maxStock = $variantStock !== null ? $variantStock : $productStock;
// Jika product stock = null (unlimited), $maxStock = 0
// qty 1 < 0 → FALSE → "Maximum stock reached" pada item unlimited!
```

**Masalah:** Product dengan stock `null` (unlimited) tidak bisa di-increment di cart karena `$maxStock = 0`.

**Solusi:**
```php
$productStock = $item->product->stock; // keep null
$variantStock = $item->variant?->stock;
$maxStock = $variantStock ?? $productStock;

if ($maxStock === null || $item->qty < $maxStock) {
    $item->increment('qty');
    ...
}
```

---

## 5. MEDIUM BUGS — Logic Defects

### BUG-M01: 🟠 Voucher `free_shipping` Discount Tidak Recalculate Saat Courier Berubah

**File:** `app/Livewire/Storefront/CheckoutView.php:241`

```php
} elseif ($voucher->type === 'free_shipping') {
    $this->discountAmount = $this->shippingCost;
}
```

**Masalah:** Jika customer apply voucher free_shipping saat shipping = RM10, lalu ganti courier ke RM15, `discountAmount` tetap RM10 (tidak update). Customer tetap bayar RM5 untuk shipping.

**Solusi:** Recalculate voucher di `updatedCourier()`:
```php
public function updatedCourier($value)
{
    if ($value && isset($this->availableCouriers[$value])) {
        $this->shippingCost = $this->availableCouriers[$value]['price'];
    }
    // Recalculate free_shipping voucher
    if ($this->voucherId) $this->applyVoucher();
}
```

---

### BUG-M02: 🟠 OrderObserver Cancel — Stock Restore Tanpa Null-Check

**File:** `app/Observers/OrderObserver.php:13-18`

```php
$item->product->increment('stock', $item->qty);
if ($item->variant) {
    $item->variant->increment('stock', $item->qty);
}
```

**Masalah:** Tidak ada null-check pada `stock`. Jika product sebelumnya unlimited stock (null), increment akan set stock ke `qty` value → **produk unlimited tiba-tiba jadi limited**.

Juga: Jika order di-cancel sebelum payment (COD/manual_transfer sudah decrement stock, tapi gateway belum), stock bisa di-restore meskipun belum pernah di-decrement.

**Solusi:**
```php
if ($item->product->stock !== null) {
    $item->product->increment('stock', $item->qty);
}
```

---

### BUG-M03: 🟠 Subtotal Tidak Recalculate Setelah Cart Refresh di placeOrder()

**File:** `app/Livewire/Storefront/CheckoutView.php:269-275`

```php
// Bug-19: Refresh cart to prevent stale state
$this->cart = Cart::with(['items.product', 'items.variant'])
    ->where('id', $this->cart->id)->first();
```

**Masalah:** Cart di-refresh tapi `$this->subtotal` TIDAK dihitung ulang. Jika harga produk berubah antara mount dan placeOrder, subtotal tetap stale → grand total salah.

**Solusi:** Recalculate subtotal setelah refresh:
```php
$this->subtotal = 0;
foreach ($this->cart->items as $item) {
    $this->subtotal += ($item->effective_price * $item->qty);
}
```

---

### BUG-M04: 🟠 Bundle Items Ditambah Tanpa Stock Check

**File:** `app/Livewire/Storefront/BundleListView.php:24-67`

```php
public function addToCart($bundleId)
{
    // ... TIDAK ADA stock check sama sekali!
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        ...
    ]);
}
```

**Masalah:** Bundle bisa di-add ke cart meskipun salah satu product dalam bundle sudah out of stock.

**Solusi:** Tambahkan stock check sebelum create:
```php
foreach ($bundle->products as $product) {
    if ($product->stock !== null && $product->stock < $product->pivot->qty) {
        $this->dispatch('notify', message: "{$product->name} is out of stock.");
        return;
    }
}
```

---

### BUG-M05: 🟠 Bundle Duplicate di Cart — Tidak Ada Merge/Check

**File:** `app/Livewire/Storefront/BundleListView.php:55-60`

**Masalah:** Setiap kali "Add Bundle to Cart" diklik, items baru di-create tanpa cek apakah bundle yang sama sudah ada di cart. Hasilnya: cart punya **duplicate bundle items** yang tidak bisa di-manage.

**Solusi:** Check existing bundle items sebelum create:
```php
$existingBundleItem = CartItem::where('cart_id', $cart->id)
    ->where('bundle_id', $bundle->id)
    ->exists();
if ($existingBundleItem) {
    // increment qty atau notify already in cart
    return;
}
```

---

## 6. LOW / UX Defects

### BUG-U01: 🟡 Voucher Code Case-Sensitive

**File:** `app/Livewire/Storefront/CheckoutView.php:215`

```php
$voucher = Voucher::where('code', $this->voucherCode)->...first();
```

**Masalah:** "SAVE10" ≠ "save10". Customer akan bingung jika voucher tidak ditemukan karena case.

**Solusi:** `->whereRaw('LOWER(code) = ?', [strtolower($this->voucherCode)])`

---

### BUG-U02: 🟡 Checkout Voucher Section Tidak Ada di Blade

**File:** `resources/views/livewire/storefront/checkout-view.blade.php`

**Masalah:** Backend support voucher (`applyVoucher()` method, `voucherCode` property), tapi di Blade template **TIDAK ADA** input field untuk voucher. Customer tidak bisa apply voucher sama sekali.

**Solusi:** Tambahkan voucher section di checkout blade sebelum order summary.

---

### BUG-U03: 🟡 FlashSaleView — Tidak Load `primaryImage` Relationship

**File:** `app/Livewire/Storefront/FlashSaleView.php:14`

```php
$q->with('primaryImage', 'images', 'reviews');
```

**Masalah:** `'images'` bukan relationship yang valid di Product model. Yang benar adalah `'productImages'`. Ini akan silently fail (return empty) — gambar tidak tampil di flash sale page.

**Solusi:** Ganti `'images'` → `'productImages'`

---

### BUG-U04: 🟡 No Loading State untuk Place Order Button

**File:** `checkout-view.blade.php:238-241`

**Masalah:** Meskipun ada `wire:loading`, button **tidak disabled** saat loading. Customer bisa double-click dan submit order 2x.

**Solusi:** Tambahkan `wire:loading.attr="disabled"` — sudah ada, tapi tambahkan juga visual disabled state:
```html
<button ... wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
```

---

### BUG-U05: 🟡 Cart Delete Cascade — Order Items Hilang

**File:** Database migration `create_orders_table.php:17`

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
```

**Masalah:** Jika user dihapus, SEMUA orders juga dihapus (cascadeOnDelete). Ini menghancurkan data historis penjualan.

**Solusi:** Ganti ke `nullOnDelete()` dan handle gracefully.

---

### BUG-U06: 🟡 ProductList — SQL Injection Potential via Search

**File:** `app/Livewire/Storefront/ProductList.php:62`

```php
$q->where('name', 'like', "%{$searchTerm}%")
```

**Masalah:** Meskipun Livewire sanitize input, menggunakan string interpolation dalam LIKE bisa menyebabkan wildcard injection (`%` dan `_` characters). Bukan SQL injection tapi bisa cause performance issues.

**Solusi:** Escape LIKE wildcards:
```php
$escaped = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);
$q->where('name', 'like', "%{$escaped}%")
```

---

### BUG-U07: 🟡 Setting::get() Tidak Cached — Banyak Query Redundan

**File:** `app/Models/Setting.php:19-30`

**Masalah:** Setiap pemanggilan `Setting::get()` melakukan query DB. Di checkout page saja ada 10+ panggilan Setting::get() (billplz_enabled, stripe_enabled, cod_enabled, dll). Helper `setting()` di `SettingHelper.php` MEMILIKI cache, tapi **`Setting::get()` model method TIDAK**.

Checkout page dipanggil melalui `Setting::get()` dan `Setting::isEnabled()` di Blade — bukan `setting()` helper.

**Solusi:** Tambahkan caching di `Setting::get()`:
```php
public static function get(string $key, mixed $default = null): mixed
{
    return cache()->rememberForever("setting:{$key}", function () use ($key, $default) {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;
        return $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;
    });
}
```

---

## 7. Filament Admin Panel Defects

### BUG-F01: 🟠 Order Edit Bisa Ubah Status Tanpa Side Effects

**File:** `app/Filament/Resources/Orders/Schemas/OrderForm.php:28-36`

**Masalah:** Admin bisa ubah status dari "pending" langsung ke "completed" tanpa trigger stock decrement atau payment update. OrderObserver hanya handle `cancelled` status. Perubahan status lain (paid → processing → shipped → completed) tidak ada logic apapun.

**Solusi:** Tambahkan logic di OrderObserver atau Filament afterSave hook.

---

### BUG-F02: 🟠 Product Form — Weight Field Tidak Ada

**File:** `app/Filament/Resources/Products/Schemas/ProductForm.php`

**Masalah:** Migration sudah menambahkan kolom `weight` ke products table, tapi Filament ProductForm **tidak memiliki input field** untuk weight. Admin tidak bisa set weight → semua produk pakai default 0.500 kg → shipping cost tidak akurat.

**Solusi:** Tambahkan di General Information tab:
```php
TextInput::make('weight')
    ->numeric()
    ->default(0.500)
    ->suffix('kg')
    ->helperText('Berat produk dalam kilogram'),
```

---

### BUG-F03: 🟡 Products Table Tidak Tampilkan Stock

**File:** `app/Filament/Resources/Products/Tables/ProductsTable.php`

**Masalah:** Kolom stock tidak ditampilkan di products table. Admin tidak bisa monitor stock level tanpa masuk ke edit page.

**Solusi:** Tambahkan kolom:
```php
TextColumn::make('stock')
    ->sortable()
    ->badge()
    ->color(fn ($state) => $state !== null && $state <= 5 ? 'danger' : 'success'),
```

---

### BUG-F04: 🟡 Order Delete Action di EditOrder — Menghapus Order + Payment + Items

**File:** `app/Filament/Resources/Orders/Pages/EditOrder.php:16`

```php
DeleteAction::make(),
```

**Masalah:** Delete order akan cascade delete payment dan order items (karena `cascadeOnDelete` di migration). Ini menghancurkan data akuntansi. Seharusnya order hanya bisa di-cancel, bukan dihapus.

**Solusi:** Replace `DeleteAction` dengan cancel action, atau tambahkan soft deletes.

---

## 8. Flowchart: Checkout Bug Map

```
┌──────────────────────────────────────────────────────────────────┐
│                     CHECKOUT FLOW BUG MAP                        │
└──────────────────────────────────────────────────────────────────┘

Customer Opens Checkout
        │
        ▼
┌───────────────┐
│ mount()       │──→ Cart loaded with items
│               │──→ Subtotal calculated     ✓ OK
│               │──→ Pre-fill user data      ✓ OK  
│               │──→ calculateShipping()     ⚠ BUG-K01: dipanggil jika postcode ≥ 5
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ User Fills    │──→ updatedCity() ──→ calculateShipping() ❌ BUG-K01: no postcode guard
│ Form Fields   │──→ updatedState() ──→ calculateShipping() ❌ BUG-K01: no postcode guard  
│               │──→ updatedPostcode() ──→ calculateShipping() ✓ OK
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ applyVoucher()│──→ Voucher validated         ⚠ BUG-U01: case-sensitive
│               │──→ discountAmount calculated  ⚠ BUG-M01: free_shipping stale
│               │──→ ❌ BUG-U02: No UI input!
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ placeOrder()  │──→ Validation                ✓ OK
│               │──→ Cart Refresh              ⚠ BUG-M03: subtotal NOT recalculated
│               │──→ Voucher increment         ❌ BUG-C05: race condition
│               │──→ Order.total = grand-disc  ✓ OK
│               │──→ Payment.amount = grand    ❌ BUG-C01: discount not applied!
│               │──→ Stock decrement (COD)     ✓ OK (with null check)
│               │──→ Cart deleted              ✓ OK
└───────┬───────┘
        │
        ├──→ COD/Manual ──→ Success Page ✓
        │
        └──→ Gateway ──→ payment.process ──→ Billplz/Stripe
                              │
                    ┌─────────┴─────────┐
                    ▼                   ▼
             billplzCallback()    stripeCallback()
                    │                   │
                    ├──→ Stock decrement ❌ BUG-C02: no null check
                    ├──→ ❌ BUG-C02: double decrement with webhook
                    │
                    ▼
             billplzWebhook()  /  stripeWebhook()
                    │
                    └──→ Stock decrement AGAIN ❌ BUG-C02: DOUBLE!
```

---

## 9. Flowchart: Stock Decrement Race Condition

```
┌─────────────────────────────────────────────────────────────┐
│              STOCK DOUBLE-DECREMENT FLOW                     │
└─────────────────────────────────────────────────────────────┘

            Customer Pays via Billplz
                     │
          ┌──────────┴──────────┐
          │                     │
    [Browser Redirect]    [Server Webhook]
          │                     │
          ▼                     ▼
   billplzCallback()     billplzWebhook()
          │                     │
    ┌─────┴─────┐         ┌─────┴─────┐
    │ Check:    │         │ Check:    │
    │ status != │         │ status != │
    │ 'paid'    │         │ 'paid'    │
    └─────┬─────┘         └─────┬─────┘
          │                     │
     Both TRUE (status = 'pending')
          │                     │
          ▼                     ▼
    Update status         Update status
    to 'paid'            to 'paid'
          │                     │
          ▼                     ▼
    ╔═══════════╗         ╔═══════════╗
    ║ DECREMENT ║         ║ DECREMENT ║
    ║ STOCK -N  ║         ║ STOCK -N  ║
    ╚═══════════╝         ╚═══════════╝
          │                     │
          └──────────┬──────────┘
                     ▼
          ╔═══════════════════╗
          ║ STOCK REDUCED 2x! ║
          ║ Product: 10 → 8   ║
          ║ Should be: 10 → 9 ║
          ╚═══════════════════╝

    ═══════════════════════════════════
    SOLUSI: Use DB::transaction + lockForUpdate()
    pada Payment record sebelum update status
    ═══════════════════════════════════
```

---

## 10. Flowchart: Payment Callback Double-Deduction

```
┌─────────────────────────────────────────────────────────────┐
│         PAYMENT AMOUNT MISMATCH FLOW (BUG-C01)              │
└─────────────────────────────────────────────────────────────┘

    Cart Items:
    ├── Product A: RM 50 x 2 = RM 100
    └── Product B: RM 30 x 1 = RM 30
    
    Subtotal = RM 130
    Shipping = RM 10
    SST 6%   = RM 7.80
    Voucher  = -RM 20 (fixed)
    
    ┌──────────────────────┐    ┌──────────────────────┐
    │   ORDER RECORD       │    │   PAYMENT RECORD     │
    │                      │    │                      │
    │ total = grandTotal   │    │ amount = grandTotal  │
    │       - discount     │    │ (NO discount!)       │
    │                      │    │                      │
    │ = 130 + 10 + 7.80   │    │ = 130 + 10 + 7.80   │
    │   - 20               │    │                      │
    │ = RM 127.80          │    │ = RM 147.80          │
    └──────────┬───────────┘    └──────────┬───────────┘
               │                           │
               │   RM 127.80 ≠ RM 147.80   │
               │                           │
               ▼                           ▼
    ╔══════════════════╗     ╔═══════════════════════╗
    ║ Order shows      ║     ║ Customer CHARGED      ║
    ║ RM 127.80        ║     ║ RM 147.80 at gateway! ║
    ╚══════════════════╝     ╚═══════════════════════╝
    
    Customer pays RM 20 MORE than expected!
```

---

## 11. Flowchart: Voucher Abuse Flow

```
┌─────────────────────────────────────────────────────────────┐
│              VOUCHER RACE CONDITION (BUG-C05)                │
└─────────────────────────────────────────────────────────────┘

    Voucher: "SAVE50" — usage_limit: 1, used_count: 0

    ┌─────────────┐              ┌─────────────┐
    │ Customer A   │              │ Customer B   │
    │ (Tab 1)      │              │ (Tab 2)      │
    └──────┬──────┘              └──────┬──────┘
           │                            │
    T1:  applyVoucher()          T1:  applyVoucher()
           │                            │
    ┌──────▼──────┐              ┌──────▼──────┐
    │ used_count=0│              │ used_count=0│
    │ < limit=1   │              │ < limit=1   │
    │ → VALID ✓   │              │ → VALID ✓   │
    └──────┬──────┘              └──────┬──────┘
           │                            │
    T2:  placeOrder()            T2:  placeOrder()
           │                            │
    ┌──────▼──────┐              ┌──────▼──────┐
    │ DB::trans    │              │ DB::trans    │
    │ increment   │              │ increment   │
    │ used_count  │              │ used_count  │
    │ → now = 1   │              │ → now = 2!  │
    └──────┬──────┘              └──────┬──────┘
           │                            │
           ▼                            ▼
    ╔══════════════╗             ╔══════════════╗
    ║ Order created ║             ║ Order created ║
    ║ with discount ║             ║ with discount ║
    ╚══════════════╝             ╚══════════════╝
    
    RESULT: Voucher used 2x! (limit was 1)
    
    FIX: lockForUpdate() + re-validate inside transaction
```

---

## 12. Flowchart: Cart Price Mismatch

```
┌─────────────────────────────────────────────────────────────┐
│            CART vs CHECKOUT PRICE MISMATCH (BUG-P01)         │
└─────────────────────────────────────────────────────────────┘

    Product: "Gaming Mouse"
    ├── Original Price: RM 100
    └── Flash Sale Price: RM 70

    ┌──────────────────────────┐
    │       CART VIEW           │
    │  (cart-view.blade.php)    │
    │                           │
    │  $price = $item->product  │
    │           ->price         │
    │         = RM 100 ❌       │
    │                           │
    │  Displayed: RM 100/item   │
    └────────────┬─────────────┘
                 │
          Customer proceeds
                 │
                 ▼
    ┌──────────────────────────┐
    │     CHECKOUT VIEW         │
    │  (CheckoutView.php)       │
    │                           │
    │  $price = $item           │
    │    ->effective_price      │
    │         = RM 70 ✓        │
    │                           │
    │  Displayed: RM 70/item    │
    └────────────┬─────────────┘
                 │
                 ▼
    ╔══════════════════════════╗
    ║ Customer sees RM 100 in  ║
    ║ cart, but RM 70 at       ║
    ║ checkout → CONFUSION     ║
    ║                          ║
    ║ Or WORSE: If flash sale  ║
    ║ ENDS between cart view   ║
    ║ and checkout, price      ║
    ║ INCREASES silently!      ║
    ╚══════════════════════════╝
```

---

## 13. Flowchart: Guest Checkout UX Issues

```
┌─────────────────────────────────────────────────────────────┐
│              GUEST CHECKOUT SESSION PROBLEM                   │
└─────────────────────────────────────────────────────────────┘

    Guest Customer places Order #101
           │
           ▼
    session('last_order_id') = 101
           │
           ▼
    Redirected to success page ✓
    Can access /checkout/success/101 ✓
           │
    Guest places ANOTHER Order #102
           │
           ▼
    session('last_order_id') = 102  (overwrites 101!)
           │
           ▼
    ┌──────────────────┐     ┌──────────────────┐
    │ Order #102       │     │ Order #101       │
    │ Accessible ✓     │     │ 403 FORBIDDEN ❌  │
    │                  │     │ Session lost!     │
    └──────────────────┘     └──────────────────┘
    
    JUGA:
    ┌──────────────────────────────────────────┐
    │ Guest → payment.process/{order}          │
    │                                          │
    │ Session check: last_order_id !== 102     │
    │ → 403! Guest LOCKED OUT of payment!      │
    │                                          │
    │ (jika session regenerate / expired)      │
    └──────────────────────────────────────────┘
    
    FIX: Store array of guest order IDs in session
    session('guest_order_ids', [101, 102, ...])
```

---

## 14. Ringkasan Solusi

### Priority 1 — HARUS SEGERA (CRITICAL)

| # | Bug | Fix |
|---|-----|-----|
| C01 | Payment amount tidak kurangi discount | `'amount' => $grandTotal - $this->discountAmount` |
| C02 | Stock double-decrement | `lockForUpdate()` pada Payment sebelum update status |
| C03 | Stock decrement tanpa null-check | Tambahkan `if ($item->product->stock !== null)` |
| C05 | Voucher race condition | `lockForUpdate()` + re-validate di dalam transaction |
| C06 | OrderHistory data leak | Fix `orWhere` menjadi nested `where` |
| C08 | Billplz callback tanpa x-signature enforcement | Reject jika x_signature key kosong |
| P01 | Cart vs Checkout harga beda | Ganti `$item->product->price` → `$item->effective_price` di cart blade |

### Priority 2 — PENTING (MEDIUM)

| # | Bug | Fix |
|---|-----|-----|
| K01 | calculateShipping() tanpa postcode guard | Cek postcode sebelum call |
| K02 | updateShipping() stuck loading | Hapus atau fix method |
| P03 | addToCart null stock crash | Null-check sebelum comparison |
| P04 | Variant matching false positive | Gunakan exact match |
| P05 | Cart increment blocked untuk unlimited stock | Null-safe comparison |
| M01 | Free shipping voucher stale | Recalculate di updatedCourier() |
| M02 | OrderObserver restore null stock | Null-check sebelum increment |
| M03 | Subtotal stale setelah cart refresh | Recalculate subtotal |
| M04 | Bundle tanpa stock check | Validasi stock sebelum add |
| M05 | Bundle duplicate di cart | Check existing sebelum create |
| F01 | Status change tanpa side effects | Tambahkan observer logic |
| F02 | Weight field tidak ada di Filament | Tambahkan TextInput weight |

### Priority 3 — NICE TO HAVE (LOW/UX)

| # | Bug | Fix |
|---|-----|-----|
| U01 | Voucher case-sensitive | `strtolower()` comparison |
| U02 | Voucher UI tidak ada di checkout | Tambahkan input di blade |
| U03 | FlashSale wrong relationship name | `'images'` → `'productImages'` |
| U06 | LIKE wildcard injection | Escape `%` dan `_` |
| U07 | Setting::get() no cache | Tambahkan cache layer |
| F03 | Stock column tidak ada di table | Tambahkan TextColumn |
| F04 | Order delete destroys data | Soft delete atau restrict |

---

> **Total bugs ditemukan: 24**  
> **Critical: 8 | Medium: 9 | Low/UX: 7**  
> 
> Bug paling berbahaya: **BUG-C01** (customer overcharged) dan **BUG-C02** (stock double-decrement) — keduanya terjadi di setiap transaksi payment gateway.

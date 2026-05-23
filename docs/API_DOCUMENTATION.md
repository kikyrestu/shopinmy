# 📦 WebEcommerceMalaysia — API Documentation

> **Stack:** Laravel 13 · PHP 8.3 · Filament Admin · Spatie Permission  
> **Auth Method:** Laravel Sanctum (Token-Based) — tambahkan `laravel/sanctum` jika belum ada  
> **Base URL:** `https://your-domain.com/api`  
> **Format:** JSON (`Content-Type: application/json`)

---

## 📋 Daftar Isi

1. [Setup & Autentikasi](#1-setup--autentikasi)
2. [Auth — Register, Login, Logout](#2-auth)
3. [User Profile & Alamat](#3-user-profile--alamat)
4. [Produk](#4-produk)
5. [Kategori](#5-kategori)
6. [Brand](#6-brand)
7. [Keranjang (Cart)](#7-keranjang-cart)
8. [Order (Pesanan)](#8-order-pesanan)
9. [Pembayaran (Payment)](#9-pembayaran-payment)
10. [Voucher](#10-voucher)
11. [Flash Sale](#11-flash-sale)
12. [Bundle Produk](#12-bundle-produk)
13. [Wishlist](#13-wishlist)
14. [Ulasan (Reviews)](#14-ulasan-reviews)
15. [Pertanyaan Produk (Q&A)](#15-pertanyaan-produk-qa)
16. [Return Request](#16-return-request)
17. [Loyalty Points](#17-loyalty-points)
18. [Restock Alert](#18-restock-alert)
19. [Banner & Halaman Statis](#19-banner--halaman-statis)
20. [Newsletter Subscriber](#20-newsletter-subscriber)
21. [Settings Publik](#21-settings-publik)
22. [Struktur Response & Error Codes](#22-struktur-response--error-codes)
23. [Model Skema Database](#23-model-skema-database)

---

## 1. Setup & Autentikasi

### Instalasi Sanctum (jika belum ada)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Di `routes/api.php`, semua route yang memerlukan login dibungkus middleware:
```php
Route::middleware('auth:sanctum')->group(function () {
    // route protected
});
```

### Header Wajib

| Header | Value | Keterangan |
|--------|-------|------------|
| `Content-Type` | `application/json` | Selalu wajib |
| `Accept` | `application/json` | Agar Laravel return JSON |
| `Authorization` | `Bearer {token}` | Wajib untuk endpoint protected |

---

## 2. Auth

### 2.1 Register

**POST** `/api/register`

Mendaftarkan pengguna baru. Tidak perlu token.

**Request Body:**
```json
{
  "name": "Ahmad Razif",
  "email": "ahmad@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ✅ | Nama lengkap, max 255 karakter |
| `email` | string | ✅ | Email unik, format valid |
| `password` | string | ✅ | Min 8 karakter (default Laravel) |
| `password_confirmation` | string | ✅ | Harus sama dengan `password` |
| `phone` | string | ❌ | Nomor telefon, nullable |

**Response 201:**
```json
{
  "message": "Register berjaya",
  "token": "1|abc123xyz...",
  "user": {
    "id": 1,
    "name": "Ahmad Razif",
    "email": "ahmad@example.com",
    "phone": "0123456789",
    "created_at": "2026-05-20T12:00:00.000000Z"
  }
}
```

---

### 2.2 Login

**POST** `/api/login`

**Request Body:**
```json
{
  "email": "ahmad@example.com",
  "password": "password123",
  "device_name": "web-browser"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `email` | string | ✅ | Email terdaftar |
| `password` | string | ✅ | Password akun |
| `device_name` | string | ❌ | Label perangkat (default: `"web"`) |

**Response 200:**
```json
{
  "message": "Login berjaya",
  "token": "2|xyz987abc...",
  "user": {
    "id": 1,
    "name": "Ahmad Razif",
    "email": "ahmad@example.com",
    "phone": "0123456789",
    "email_verified_at": null
  }
}
```

**Response 422 (Gagal):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

---

### 2.3 Logout

**POST** `/api/logout` 🔒 *(Protected)*

Hapus token aktif.

**Response 200:**
```json
{
  "message": "Logout berjaya"
}
```

---

### 2.4 Forgot Password

**POST** `/api/forgot-password`

Kirim email reset password.

**Request Body:**
```json
{
  "email": "ahmad@example.com"
}
```

**Response 200:**
```json
{
  "message": "Link reset password telah dihantar ke email anda"
}
```

---

### 2.5 Reset Password

**POST** `/api/reset-password`

**Request Body:**
```json
{
  "token": "abc123resettoken",
  "email": "ahmad@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response 200:**
```json
{
  "message": "Password berjaya ditukar"
}
```

---

## 3. User Profile & Alamat

### 3.1 Get Profile

**GET** `/api/profile` 🔒

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "Ahmad Razif",
    "email": "ahmad@example.com",
    "phone": "0123456789",
    "email_verified_at": "2026-05-20T08:00:00.000000Z",
    "created_at": "2026-05-20T07:00:00.000000Z"
  }
}
```

---

### 3.2 Update Profile

**PUT** `/api/profile` 🔒

**Request Body:**
```json
{
  "name": "Ahmad Razif Bin Ali",
  "phone": "0199887766"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ❌ | Max 255 karakter |
| `phone` | string | ❌ | Nombor telefon baru |

**Response 200:**
```json
{
  "message": "Profil berjaya dikemaskini",
  "data": { ... }
}
```

---

### 3.3 Tukar Password

**PUT** `/api/profile/password` 🔒

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### 3.4 Senarai Alamat

**GET** `/api/addresses` 🔒

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "label": "Rumah",
      "address": "No 12, Jalan Bunga Raya",
      "city": "Kuala Lumpur",
      "postcode": "50000",
      "state": "Wilayah Persekutuan",
      "created_at": "2026-05-20T07:00:00.000000Z"
    }
  ]
}
```

---

### 3.5 Tambah Alamat

**POST** `/api/addresses` 🔒

**Request Body:**
```json
{
  "label": "Pejabat",
  "address": "Level 5, Menara KL Sentral",
  "city": "Kuala Lumpur",
  "postcode": "50470",
  "state": "Wilayah Persekutuan"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `label` | string | ❌ | Contoh: "Rumah", "Pejabat" |
| `address` | string | ✅ | Alamat penuh |
| `city` | string | ✅ | Bandar |
| `postcode` | string | ✅ | Poskod Malaysia |
| `state` | string | ✅ | Negeri (contoh: "Selangor") |

**Response 201:**
```json
{
  "message": "Alamat berjaya ditambah",
  "data": { ... }
}
```

---

### 3.6 Kemaskini Alamat

**PUT** `/api/addresses/{id}` 🔒

Body sama dengan tambah alamat. Semua field optional.

---

### 3.7 Padam Alamat

**DELETE** `/api/addresses/{id}` 🔒

**Response 200:**
```json
{
  "message": "Alamat berjaya dipadam"
}
```

---

## 4. Produk

### 4.1 Senarai Produk

**GET** `/api/products`

**Query Parameters:**

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `page` | integer | Nombor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 20, max: 100) |
| `search` | string | Cari nama produk |
| `category_id` | integer | Filter by kategori |
| `brand_id` | integer | Filter by brand |
| `min_price` | decimal | Harga minimum |
| `max_price` | decimal | Harga maksimum |
| `sort` | string | `price_asc`, `price_desc`, `newest`, `popular` |
| `is_active` | boolean | Default: `true` |

**Contoh Request:**
```
GET /api/products?category_id=3&min_price=10&max_price=100&sort=price_asc&page=1
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Baju Melayu Klasik",
      "slug": "baju-melayu-klasik",
      "description": "Baju Melayu berkualiti tinggi...",
      "price": "89.90",
      "is_active": true,
      "meta_title": "Baju Melayu Klasik | Toko Malaysia",
      "meta_description": "...",
      "meta_keywords": "baju melayu, pakaian tradisional",
      "category": {
        "id": 3,
        "name": "Pakaian Lelaki",
        "slug": "pakaian-lelaki"
      },
      "brand": {
        "id": 2,
        "name": "BrandX",
        "slug": "brandx"
      },
      "primary_image": {
        "id": 5,
        "path": "/storage/products/baju1.jpg",
        "is_primary": true
      },
      "images": [
        { "id": 5, "path": "/storage/products/baju1.jpg", "is_primary": true, "sort": 0 },
        { "id": 6, "path": "/storage/products/baju2.jpg", "is_primary": false, "sort": 1 }
      ],
      "variants_count": 3,
      "created_at": "2026-05-20T07:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  }
}
```

---

### 4.2 Detail Produk

**GET** `/api/products/{slug}`

Gunakan **slug** bukan ID untuk SEO-friendly URL.

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "Baju Melayu Klasik",
    "slug": "baju-melayu-klasik",
    "description": "Baju Melayu berkualiti tinggi...",
    "price": "89.90",
    "is_active": true,
    "category": { "id": 3, "name": "Pakaian Lelaki", "slug": "pakaian-lelaki" },
    "brand": { "id": 2, "name": "BrandX", "slug": "brandx" },
    "images": [
      { "id": 5, "path": "/storage/products/baju1.jpg", "is_primary": true, "sort": 0 }
    ],
    "variants": [
      {
        "id": 10,
        "name": "Saiz",
        "value": "S",
        "price_modifier": "0.00",
        "stock": 25,
        "sku": "BMK-S"
      },
      {
        "id": 11,
        "name": "Saiz",
        "value": "M",
        "price_modifier": "0.00",
        "stock": 10,
        "sku": "BMK-M"
      }
    ],
    "reviews_avg_rating": 4.5,
    "reviews_count": 12,
    "questions": [
      {
        "id": 1,
        "question": "Adakah tersedia dalam warna putih?",
        "answer": "Ya, tersedia",
        "user": { "name": "Siti" },
        "created_at": "2026-05-18T10:00:00.000000Z"
      }
    ],
    "meta_title": "Baju Melayu Klasik",
    "meta_description": "...",
    "meta_keywords": "..."
  }
}
```

---

### 4.3 Produk Terkait

**GET** `/api/products/{slug}/related`

Mengembalikan produk dari kategori yang sama.

**Response 200:**
```json
{
  "data": [ ... ] // array produk (max 8)
}
```

---

## 5. Kategori

### 5.1 Senarai Kategori

**GET** `/api/categories`

Termasuk kategori parent dan anak (children).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Pakaian",
      "slug": "pakaian",
      "image": "/storage/categories/pakaian.jpg",
      "parent_id": null,
      "children": [
        {
          "id": 3,
          "name": "Pakaian Lelaki",
          "slug": "pakaian-lelaki",
          "image": null,
          "parent_id": 1,
          "children": []
        }
      ]
    }
  ]
}
```

---

### 5.2 Detail Kategori

**GET** `/api/categories/{slug}`

**Response 200:**
```json
{
  "data": {
    "id": 3,
    "name": "Pakaian Lelaki",
    "slug": "pakaian-lelaki",
    "image": null,
    "parent": { "id": 1, "name": "Pakaian", "slug": "pakaian" },
    "children": []
  }
}
```

---

## 6. Brand

### 6.1 Senarai Brand

**GET** `/api/brands`

**Response 200:**
```json
{
  "data": [
    { "id": 1, "name": "BrandX", "slug": "brandx" },
    { "id": 2, "name": "MalaysiaCraft", "slug": "malaysiacraft" }
  ]
}
```

---

## 7. Keranjang (Cart)

Semua endpoint cart memerlukan autentikasi.

### 7.1 Lihat Cart

**GET** `/api/cart` 🔒

**Response 200:**
```json
{
  "data": {
    "id": 5,
    "items": [
      {
        "id": 12,
        "product": {
          "id": 1,
          "name": "Baju Melayu Klasik",
          "slug": "baju-melayu-klasik",
          "price": "89.90",
          "primary_image": { "path": "/storage/products/baju1.jpg" }
        },
        "variant": {
          "id": 10,
          "name": "Saiz",
          "value": "M",
          "price_modifier": "0.00",
          "stock": 10
        },
        "qty": 2,
        "subtotal": "179.80"
      }
    ],
    "total": "179.80",
    "items_count": 2
  }
}
```

---

### 7.2 Tambah Item ke Cart

**POST** `/api/cart/items` 🔒

**Request Body:**
```json
{
  "product_id": 1,
  "variant_id": 10,
  "qty": 2
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `product_id` | integer | ✅ | ID produk |
| `variant_id` | integer | ❌ | ID varian (wajib jika produk ada varian) |
| `qty` | integer | ✅ | Kuantiti (min: 1) |

**Response 200:**
```json
{
  "message": "Produk berjaya ditambah ke keranjang",
  "cart": { ... }
}
```

**Response 422 (Stok Tidak Cukup):**
```json
{
  "message": "Stok tidak mencukupi",
  "errors": { "qty": ["Stok tersedia hanya 10 unit"] }
}
```

---

### 7.3 Kemaskini Kuantiti

**PUT** `/api/cart/items/{cart_item_id}` 🔒

**Request Body:**
```json
{
  "qty": 3
}
```

---

### 7.4 Buang Item dari Cart

**DELETE** `/api/cart/items/{cart_item_id}` 🔒

**Response 200:**
```json
{
  "message": "Item berjaya dibuang dari keranjang"
}
```

---

### 7.5 Kosongkan Cart

**DELETE** `/api/cart` 🔒

**Response 200:**
```json
{
  "message": "Keranjang berjaya dikosongkan"
}
```

---

## 8. Order (Pesanan)

### 8.1 Buat Pesanan

**POST** `/api/orders` 🔒

**Request Body:**
```json
{
  "address_id": 1,
  "voucher_code": "SAVE20",
  "courier": "J&T Express",
  "notes": "Tolong hantar sebelum 5PM"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `address_id` | integer | ✅ | ID alamat penghantaran |
| `voucher_code` | string | ❌ | Kod voucher diskaun |
| `courier` | string | ❌ | Nama kurier |
| `notes` | string | ❌ | Nota tambahan |

**Response 201:**
```json
{
  "message": "Pesanan berjaya dibuat",
  "data": {
    "id": 101,
    "status": "pending",
    "total": "179.80",
    "shipping_cost": "8.00",
    "tax_rate": "6.00",
    "tax_amount": "10.79",
    "grand_total": "198.59",
    "courier": "J&T Express",
    "tracking_no": null,
    "address": {
      "label": "Rumah",
      "address": "No 12, Jalan Bunga Raya",
      "city": "Kuala Lumpur",
      "postcode": "50000",
      "state": "Wilayah Persekutuan"
    },
    "items": [
      {
        "id": 20,
        "product_name": "Baju Melayu Klasik",
        "variant": "Saiz: M",
        "qty": 2,
        "price": "89.90",
        "subtotal": "179.80"
      }
    ],
    "created_at": "2026-05-20T14:00:00.000000Z"
  }
}
```

---

### 8.2 Senarai Pesanan Saya

**GET** `/api/orders` 🔒

**Query Parameters:**

| Parameter | Keterangan |
|-----------|------------|
| `status` | `pending`, `processing`, `shipped`, `delivered`, `cancelled` |
| `page` | Nombor halaman |

**Response 200:**
```json
{
  "data": [
    {
      "id": 101,
      "status": "pending",
      "total": "179.80",
      "shipping_cost": "8.00",
      "items_count": 2,
      "created_at": "2026-05-20T14:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 2, "total": 15 }
}
```

---

### 8.3 Detail Pesanan

**GET** `/api/orders/{id}` 🔒

**Response 200:**
```json
{
  "data": {
    "id": 101,
    "status": "shipped",
    "total": "179.80",
    "shipping_cost": "8.00",
    "tax_rate": "6.00",
    "tax_amount": "10.79",
    "courier": "J&T Express",
    "tracking_no": "JT1234567MY",
    "address": { ... },
    "items": [ ... ],
    "payment": {
      "id": 55,
      "type": "bank_transfer",
      "method": "Maybank",
      "status": "verified",
      "amount": "198.59",
      "verified_at": "2026-05-20T16:00:00.000000Z"
    },
    "voucher": {
      "code": "SAVE20",
      "type": "percentage",
      "value": "20.00"
    }
  }
}
```

**Status Pesanan:**

| Status | Maksud |
|--------|--------|
| `pending` | Menunggu pembayaran |
| `processing` | Pembayaran diterima, sedang diproses |
| `shipped` | Telah dihantar |
| `delivered` | Telah diterima |
| `cancelled` | Dibatalkan |

---

### 8.4 Batal Pesanan

**POST** `/api/orders/{id}/cancel` 🔒

Hanya boleh dibatalkan jika status masih `pending`.

**Response 200:**
```json
{
  "message": "Pesanan berjaya dibatalkan"
}
```

---

## 9. Pembayaran (Payment)

### 9.1 Senarai Akaun Bank

**GET** `/api/bank-accounts`

Mengembalikan akaun bank aktif untuk rujukan pembayaran.

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "bank_name": "Maybank",
      "account_name": "Syarikat Malaysia Sdn Bhd",
      "account_number": "1234567890",
      "logo": "/storage/banks/maybank.png",
      "sort": 0
    },
    {
      "id": 2,
      "bank_name": "CIMB Bank",
      "account_name": "Syarikat Malaysia Sdn Bhd",
      "account_number": "0987654321",
      "logo": "/storage/banks/cimb.png",
      "sort": 1
    }
  ]
}
```

---

### 9.2 Submit Bukti Pembayaran

**POST** `/api/orders/{order_id}/payment` 🔒

Gunakan `multipart/form-data` untuk upload imej bukti bayar.

**Request Body (Form Data):**

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `method` | string | ✅ | Nama bank, contoh: `"Maybank"` |
| `type` | string | ✅ | `"bank_transfer"`, `"online_banking"`, `"ewallet"` |
| `reference` | string | ❌ | Nombor rujukan transaksi |
| `amount` | decimal | ✅ | Jumlah yang dibayar |
| `proof_image` | file | ❌ | Imej bukti (jpg/png, max 2MB) |

**Response 201:**
```json
{
  "message": "Bukti pembayaran berjaya dihantar. Menunggu pengesahan.",
  "data": {
    "id": 55,
    "order_id": 101,
    "method": "Maybank",
    "type": "bank_transfer",
    "status": "pending",
    "reference": "TXN20260520001",
    "amount": "198.59",
    "proof_image": "/storage/payments/proof_55.jpg",
    "created_at": "2026-05-20T14:30:00.000000Z"
  }
}
```

**Status Pembayaran:**

| Status | Maksud |
|--------|--------|
| `pending` | Menunggu pengesahan admin |
| `verified` | Pembayaran disahkan |
| `rejected` | Pembayaran ditolak (lihat `rejection_reason`) |

---

## 10. Voucher

### 10.1 Semak / Guna Voucher

**POST** `/api/vouchers/check` 🔒

**Request Body:**
```json
{
  "code": "SAVE20",
  "order_total": 179.80
}
```

**Response 200 (Voucher Valid):**
```json
{
  "valid": true,
  "data": {
    "id": 3,
    "code": "SAVE20",
    "type": "percentage",
    "value": "20.00",
    "min_order": "50.00",
    "expires_at": "2026-12-31T23:59:59.000000Z",
    "discount_amount": "35.96",
    "final_total": "143.84"
  }
}
```

**Response 422 (Tidak Valid):**
```json
{
  "valid": false,
  "message": "Kod voucher tidak sah atau sudah tamat tempoh"
}
```

**Jenis Voucher (`type`):**

| Type | Keterangan |
|------|------------|
| `percentage` | Diskaun peratusan (`value` = %) |
| `fixed` | Diskaun tetap dalam RM (`value` = RM) |
| `free_shipping` | Penghantaran percuma |

---

## 11. Flash Sale

### 11.1 Senarai Flash Sale Aktif

**GET** `/api/flash-sales`

Hanya mengembalikan flash sale yang sedang berlangsung.

**Response 200:**
```json
{
  "data": [
    {
      "id": 2,
      "name": "Flash Sale Hari Raya",
      "slug": "flash-sale-hari-raya",
      "description": "Diskaun sehingga 50%!",
      "starts_at": "2026-05-20T00:00:00.000000Z",
      "ends_at": "2026-05-20T23:59:59.000000Z",
      "is_running": true,
      "products": [
        {
          "id": 1,
          "name": "Baju Melayu Klasik",
          "slug": "baju-melayu-klasik",
          "original_price": "89.90",
          "sale_price": "44.90",
          "discount_percentage": 50,
          "qty_remaining": 15,
          "primary_image": { "path": "/storage/products/baju1.jpg" }
        }
      ]
    }
  ]
}
```

---

### 11.2 Detail Flash Sale

**GET** `/api/flash-sales/{slug}`

**Response 200:**
```json
{
  "data": {
    "id": 2,
    "name": "Flash Sale Hari Raya",
    "slug": "flash-sale-hari-raya",
    "description": "...",
    "starts_at": "2026-05-20T00:00:00.000000Z",
    "ends_at": "2026-05-20T23:59:59.000000Z",
    "is_running": true,
    "seconds_remaining": 36000,
    "products": [ ... ]
  }
}
```

> **Nota:** Gunakan `seconds_remaining` untuk countdown timer di frontend.

---

## 12. Bundle Produk

### 12.1 Senarai Bundle

**GET** `/api/bundles`

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bundle Hari Raya",
      "slug": "bundle-hari-raya",
      "description": "Jimat lebih dengan bundle",
      "price": "149.90",
      "is_active": true,
      "products": [
        {
          "id": 1,
          "name": "Baju Melayu Klasik",
          "qty": 1,
          "original_price": "89.90"
        },
        {
          "id": 5,
          "name": "Sampin Songket",
          "qty": 1,
          "original_price": "79.90"
        }
      ],
      "savings": "19.90"
    }
  ]
}
```

---

### 12.2 Detail Bundle

**GET** `/api/bundles/{slug}`

---

## 13. Wishlist

### 13.1 Senarai Wishlist

**GET** `/api/wishlist` 🔒

**Response 200:**
```json
{
  "data": [
    {
      "id": 3,
      "product": {
        "id": 1,
        "name": "Baju Melayu Klasik",
        "slug": "baju-melayu-klasik",
        "price": "89.90",
        "primary_image": { "path": "/storage/products/baju1.jpg" },
        "is_active": true
      },
      "created_at": "2026-05-15T09:00:00.000000Z"
    }
  ]
}
```

---

### 13.2 Tambah ke Wishlist

**POST** `/api/wishlist` 🔒

**Request Body:**
```json
{
  "product_id": 1
}
```

**Response 201:**
```json
{
  "message": "Produk berjaya ditambah ke senarai keinginan"
}
```

---

### 13.3 Buang dari Wishlist

**DELETE** `/api/wishlist/{product_id}` 🔒

**Response 200:**
```json
{
  "message": "Produk berjaya dibuang dari senarai keinginan"
}
```

---

## 14. Ulasan (Reviews)

### 14.1 Senarai Ulasan Produk

**GET** `/api/products/{product_id}/reviews`

**Query Parameters:**

| Parameter | Keterangan |
|-----------|------------|
| `rating` | Filter by rating (1-5) |
| `page` | Nombor halaman |

**Response 200:**
```json
{
  "data": [
    {
      "id": 7,
      "rating": 5,
      "comment": "Kualiti sangat bagus, jahitan kemas",
      "user": { "name": "Ahmad R." },
      "order_id": 95,
      "created_at": "2026-05-10T10:00:00.000000Z"
    }
  ],
  "summary": {
    "average": 4.5,
    "total": 12,
    "by_rating": {
      "5": 8,
      "4": 2,
      "3": 1,
      "2": 0,
      "1": 1
    }
  },
  "meta": { ... }
}
```

---

### 14.2 Tulis Ulasan

**POST** `/api/reviews` 🔒

Hanya boleh ulasan produk yang sudah dibeli (`order_id` disyorkan untuk verifikasi).

**Request Body:**
```json
{
  "product_id": 1,
  "order_id": 95,
  "rating": 5,
  "comment": "Kualiti sangat bagus!"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `product_id` | integer | ✅ | ID produk yang diulas |
| `order_id` | integer | ❌ | ID pesanan (untuk verified buyer badge) |
| `rating` | integer | ✅ | 1 hingga 5 |
| `comment` | string | ❌ | Teks ulasan |

---

## 15. Pertanyaan Produk (Q&A)

### 15.1 Tanya Soalan

**POST** `/api/products/{product_id}/questions` 🔒

**Request Body:**
```json
{
  "question": "Adakah baju ini tersedia dalam warna biru?"
}
```

**Response 201:**
```json
{
  "message": "Soalan anda telah dihantar dan menunggu kelulusan",
  "data": {
    "id": 8,
    "question": "Adakah baju ini tersedia dalam warna biru?",
    "answer": null,
    "is_published": false,
    "created_at": "2026-05-20T15:00:00.000000Z"
  }
}
```

> **Nota:** Soalan baru `is_published = false`. Admin perlu luluskan dahulu sebelum ia kelihatan di halaman produk.

---

### 15.2 Senarai Soalan (Published)

**GET** `/api/products/{product_id}/questions`

Hanya mengembalikan soalan yang sudah dijawab dan diterbitkan.

---

## 16. Return Request

### 16.1 Buat Permintaan Return

**POST** `/api/returns` 🔒

**Request Body:**
```json
{
  "order_id": 95,
  "reason": "Saiz tidak sesuai",
  "items": [
    {
      "order_item_id": 20,
      "qty": 1
    }
  ]
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `order_id` | integer | ✅ | ID pesanan |
| `reason` | string | ✅ | Sebab return |
| `items` | array | ✅ | Item yang ingin di-return |
| `items[].order_item_id` | integer | ✅ | ID item dalam pesanan |
| `items[].qty` | integer | ✅ | Kuantiti yang di-return |

**Response 201:**
```json
{
  "message": "Permintaan return berjaya dihantar",
  "data": {
    "id": 12,
    "order_id": 95,
    "reason": "Saiz tidak sesuai",
    "status": "pending",
    "items": [ ... ],
    "created_at": "2026-05-20T16:00:00.000000Z"
  }
}
```

**Status Return:**

| Status | Maksud |
|--------|--------|
| `pending` | Menunggu semakan admin |
| `approved` | Diluluskan |
| `rejected` | Ditolak |
| `completed` | Selesai |

---

### 16.2 Senarai Return Saya

**GET** `/api/returns` 🔒

---

### 16.3 Detail Return

**GET** `/api/returns/{id}` 🔒

---

## 17. Loyalty Points

### 17.1 Lihat Jumlah Points

**GET** `/api/loyalty-points/balance` 🔒

**Response 200:**
```json
{
  "data": {
    "total_points": 350,
    "points_value_rm": "3.50"
  }
}
```

---

### 17.2 Sejarah Transaksi Points

**GET** `/api/loyalty-points` 🔒

**Response 200:**
```json
{
  "data": [
    {
      "id": 5,
      "points": 100,
      "type": "earn",
      "description": "Pembelian Pesanan #95",
      "ref_id": 95,
      "created_at": "2026-05-10T12:00:00.000000Z"
    },
    {
      "id": 6,
      "points": -50,
      "type": "redeem",
      "description": "Guna semasa Pesanan #101",
      "ref_id": 101,
      "created_at": "2026-05-20T14:00:00.000000Z"
    }
  ],
  "meta": { ... }
}
```

**Jenis Transaksi (`type`):**

| Type | Maksud |
|------|--------|
| `earn` | Points diperoleh (positif) |
| `redeem` | Points digunakan (negatif) |
| `bonus` | Bonus khas |
| `expire` | Points tamat tempoh |

---

## 18. Restock Alert

### 18.1 Daftar Alert Restock

**POST** `/api/restock-alerts`

Boleh dilakukan tanpa login (guest), tapi jika login, email diisi automatik.

**Request Body:**
```json
{
  "product_id": 1,
  "variant_id": 10,
  "email": "ahmad@example.com"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `product_id` | integer | ✅ | ID produk |
| `variant_id` | integer | ❌ | ID varian (jika ada) |
| `email` | string | ✅ | Email untuk notifikasi |

**Response 201:**
```json
{
  "message": "Anda akan dimaklumkan apabila stok tersedia"
}
```

---

## 19. Banner & Halaman Statis

### 19.1 Senarai Banner Aktif

**GET** `/api/banners`

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Jualan Raya 2026",
      "image": "/storage/banners/raya2026.jpg",
      "link": "/flash-sales/flash-sale-hari-raya",
      "is_active": true,
      "sort": 0
    }
  ]
}
```

---

### 19.2 Halaman Statis

**GET** `/api/pages/{slug}`

Contoh: `GET /api/pages/tentang-kami`

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "title": "Tentang Kami",
    "slug": "tentang-kami",
    "content": "<p>Kami adalah syarikat e-dagang...</p>"
  }
}
```

> **Nota:** `content` mengandungi HTML, gunakan `v-html` (Vue) atau `dangerouslySetInnerHTML` (React) untuk render.

---

## 20. Newsletter Subscriber

### 20.1 Subscribe Newsletter

**POST** `/api/newsletter/subscribe`

**Request Body:**
```json
{
  "email": "pelanggan@example.com",
  "name": "Pelanggan Setia"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `email` | string | ✅ | Email unik |
| `name` | string | ❌ | Nama (optional) |

**Response 200:**
```json
{
  "message": "Anda berjaya melanggan newsletter kami"
}
```

---

### 20.2 Unsubscribe Newsletter

**POST** `/api/newsletter/unsubscribe`

**Request Body:**
```json
{
  "email": "pelanggan@example.com"
}
```

---

## 21. Settings Publik

### 21.1 Get Public Settings

**GET** `/api/settings`

Hanya mengembalikan settings yang tidak dienkripsi dan sesuai untuk publik.

**Response 200:**
```json
{
  "data": {
    "site_name": "TokoCraft Malaysia",
    "site_tagline": "Belanja Mudah, Jimat Selalu",
    "contact_email": "support@tocoraft.my",
    "contact_phone": "03-12345678",
    "address": "Kuala Lumpur, Malaysia",
    "facebook_url": "https://facebook.com/tococraft",
    "instagram_url": "https://instagram.com/tococraft",
    "shipping_free_min": "100.00",
    "currency": "MYR",
    "tax_rate": "6"
  }
}
```

---

## 22. Struktur Response & Error Codes

### Format Response Berjaya

```json
{
  "message": "Operasi berjaya",
  "data": { ... }
}
```

### Format Response Error

```json
{
  "message": "Keterangan error",
  "errors": {
    "field_name": ["Mesej error spesifik"]
  }
}
```

### HTTP Status Codes

| Kod | Maksud | Bila digunakan |
|-----|--------|----------------|
| `200` | OK | Request berjaya |
| `201` | Created | Resource berjaya dicipta |
| `204` | No Content | Padam berjaya |
| `400` | Bad Request | Request tidak sah |
| `401` | Unauthorized | Token tidak ada / tidak valid |
| `403` | Forbidden | Tiada kebenaran akses |
| `404` | Not Found | Resource tidak ditemui |
| `422` | Unprocessable Entity | Validation error |
| `429` | Too Many Requests | Rate limit dicapai |
| `500` | Server Error | Error dalaman server |

---

### Pagination

Semua endpoint senarai menggunakan struktur pagination berikut:

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 20,
    "to": 20,
    "total": 98
  },
  "links": {
    "first": "/api/products?page=1",
    "last": "/api/products?page=5",
    "prev": null,
    "next": "/api/products?page=2"
  }
}
```

---

## 23. Model Skema Database

### Ringkasan Semua Tabel

| Model | Tabel | Keterangan |
|-------|-------|------------|
| `User` | `users` | Pengguna, roles via Spatie |
| `Address` | `addresses` | Alamat penghantaran pengguna |
| `Category` | `categories` | Kategori produk (hierarki) |
| `Brand` | `brands` | Jenama produk |
| `Product` | `products` | Katalog produk |
| `ProductVariant` | `product_variants` | Varian (saiz, warna, dll) |
| `ProductImage` | `product_images` | Galeri imej produk |
| `Cart` | `carts` | Keranjang belanja per user |
| `CartItem` | `cart_items` | Item dalam keranjang |
| `Order` | `orders` | Pesanan |
| `OrderItem` | `order_items` | Item dalam pesanan |
| `Payment` | `payments` | Pembayaran |
| `Voucher` | `vouchers` | Kod diskaun |
| `FlashSale` | `flash_sales` | Flash sale event |
| `FlashSaleProduct` | `flash_sale_products` | Produk dalam flash sale |
| `Bundle` | `bundles` | Bundle produk |
| `BundleProduct` | `bundle_products` | Produk dalam bundle |
| `Banner` | `banners` | Banner homepage |
| `Review` | `reviews` | Ulasan produk |
| `Wishlist` | `wishlists` | Senarai keinginan |
| `ProductQuestion` | `product_questions` | Q&A produk |
| `ReturnRequest` | `returns` | Permintaan pulangan |
| `ReturnItem` | `return_items` | Item yang dipulangkan |
| `LoyaltyPoint` | `loyalty_points` | Poin kesetiaan |
| `Referral` | `referrals` | Program rujukan |
| `RestockAlert` | `restock_alerts` | Alert stok habis |
| `AbandonedCart` | `abandoned_carts` | Keranjang terbiar |
| `NewsletterSubscriber` | `newsletter_subscribers` | Pelanggan newsletter |
| `Page` | `pages` | Halaman kandungan statis |
| `Setting` | `settings` | Tetapan sistem |
| `BankAccount` | `bank_accounts` | Akaun bank pembayaran |

---

### Skema Order

```
orders
├── id
├── user_id → users.id
├── address_id → addresses.id
├── voucher_id → vouchers.id (nullable)
├── status: pending|processing|shipped|delivered|cancelled
├── total (decimal 10,2)
├── shipping_cost (decimal 10,2)
├── tax_rate (decimal 5,2)
├── tax_amount (decimal 10,2)
├── courier (nullable)
├── tracking_no (nullable)
├── guest_email (nullable)
├── guest_name (nullable)
├── guest_phone (nullable)
└── timestamps
```

### Skema Product

```
products
├── id
├── category_id → categories.id
├── brand_id → brands.id (nullable)
├── name
├── slug (unique)
├── description (nullable)
├── price (decimal 10,2)
├── images (JSON array, legacy)
├── is_active (boolean)
├── meta_title (nullable)
├── meta_description (nullable)
├── meta_keywords (nullable)
└── timestamps
```

### Skema Payment

```
payments
├── id
├── order_id → orders.id
├── type: bank_transfer|online_banking|ewallet
├── method (nama bank/platform)
├── status: pending|verified|rejected
├── reference (nullable)
├── amount (decimal 10,2)
├── proof_image (nullable)
├── rejection_reason (nullable)
├── verified_at (nullable)
├── verified_by → users.id (nullable)
└── timestamps
```

---

## 💡 Tips Untuk Frontend Developer

### 1. Autentikasi — Simpan Token

```javascript
// Selepas login, simpan token
localStorage.setItem('auth_token', response.data.token)

// Sertakan dalam setiap request
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
axios.defaults.headers.common['Accept'] = 'application/json'
```

### 2. Interceptor untuk Handle 401

```javascript
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)
```

### 3. Upload Bukti Pembayaran

```javascript
const formData = new FormData()
formData.append('method', 'Maybank')
formData.append('type', 'bank_transfer')
formData.append('amount', '198.59')
formData.append('proof_image', file) // File object

axios.post(`/api/orders/${orderId}/payment`, formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
})
```

### 4. Countdown Timer Flash Sale

```javascript
const secondsRemaining = flashSale.seconds_remaining
const endTime = new Date(flashSale.ends_at)
// Gunakan date-fns atau dayjs untuk display countdown
```

### 5. Format Harga Malaysia

```javascript
const formatPrice = (amount) => {
  return new Intl.NumberFormat('ms-MY', {
    style: 'currency',
    currency: 'MYR'
  }).format(amount)
}
// Output: RM 89.90
```

---

*Dokumentasi ini dijana daripada analisis source code projek WebEcommerceMalaysia (Laravel 13). Endpoint perlu dibuat di `routes/api.php` dan controller masing-masing sebelum boleh digunakan.*

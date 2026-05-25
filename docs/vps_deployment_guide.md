# Panduan Ternak Website di VPS (Multi-Toko)

Panduan ini didesain khusus agar Anda bisa membuat **Puluhan Toko Online Berbeda** di dalam **1 VPS yang sama**. Sistem sudah dilengkapi "Polisi Lalu Lintas" (*Reverse Proxy*) yang akan mengatur rute otomatis dan memberikan gembok hijau (HTTPS/SSL) ke masing-masing toko.

## Tahap 1: Mengarahkan Domain / Subdomain ke VPS (Wajib)
Sistem ini bebas menggunakan **Domain Utama** (contoh: `toko1.com`) maupun **Subdomain** (contoh: `cabang.toko1.com`). Sebelum menginstall, pastikan domain tersebut sudah menunjuk ke VPS.
1. Beli VPS kosong (Rekomendasi: Ubuntu 22.04 LTS atau 24.04 LTS). Catat **IP VPS** Anda (contoh: `123.45.67.89`).
2. Login ke tempat Anda membeli domain (Niagahoster, Cloudflare, dll).
3. Buka menu **DNS Management / DNS Zone**.
4. Buat Record baru dengan format:
   - Type: **A**
   - Name/Host:
     - Isi dengan **`@`** (jika pakai Domain Utama).
     - Isi dengan **kata depannya saja** (contoh: isi `cabang` jika ingin membuat `cabang.toko1.com`).
   - IPv4 Address: **Masukkan IP VPS Anda**
   - *Simpan.*
5. *(Opsional)* Jika pakai Domain Utama, buat satu Record lagi untuk "www":
   - Type: **CNAME**
   - Name/Host: **www**
   - Target: **namadomainanda.com**
   - *Simpan.*
6. Tunggu 5-10 menit agar sistem DNS global menyebar.

## Tahap 2: Menjalankan Auto-Installer
Setelah domain siap, Anda bisa langsung menginstall toko.
1. Hubungkan komputer Anda ke VPS menggunakan Terminal, Putty, atau Termius (Login sebagai `root`).
2. **Salin (Copy) dan Tempel (Paste)** satu baris kode di bawah ini lalu tekan Enter:
   ```bash
   bash <(curl -s https://raw.githubusercontent.com/kikyrestu/shopinmy/main/install.sh)
   ```
3. Script akan meminta Anda memasukkan 4 data:
   - **Kode Toko**: Gunakan kata unik tanpa spasi (contoh: `toko1`, `tokobaju`, `cabangbali`). Ini untuk memisahkan folder mesin toko Anda.
   - **Nama Domain**: Masukkan domain yang sudah di-setting di Tahap 1 (contoh: `toko1.com`).
   - **Email**: Masukkan email Anda (Hanya dipakai untuk menerbitkan sertifikat HTTPS gratis).
   - **Password Database**: Bebas.
4. Duduk manis! Sistem akan men-download aplikasi, menyalakan web, dan memasang gembok hijau secara ajaib.

## Tahap 3: Menambah Toko Baru (Toko Ke-2, Ke-3, dst)
Ingin membuat toko baru dengan domain `toko2.com`?
Gampang! Ulangi saja Tahap 1 dan Tahap 2 di atas.
Penting: Pastikan Anda memasukkan **Kode Toko yang BERBEDA** (contoh: `toko2`) dan **Domain yang BERBEDA**. Sistem tidak akan bentrok dan toko baru akan hidup berdampingan dengan damai.

## Tahap 4: Import Database Lama (Bila Ada)
Jika Anda memiliki data pelanggan dari server lama (file `.sql`), upload file tersebut ke folder VPS Anda (misalnya ke `/var/www/stores/toko1`), lalu jalankan perintah ini:
```bash
# Ganti 'toko1' dengan KODE TOKO Anda, dan masukkan password database Anda.
docker exec -i toko1_db mysql -u shopinmy_user -p'PASSWORD_DATABASE_ANDA' shopinmy < nama_file_database_lama.sql
```

Selesai! Anda resmi menjadi Juragan Website!

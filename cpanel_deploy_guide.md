# Panduan Upload ShopinMy ke cPanel

Panduan ini dibuat khusus untuk mempermudah upload aplikasi ke cPanel tanpa merusak keamanan, sehingga file password `.env` tidak akan bisa di-download oleh *hacker*.

## Langkah 1: Persiapan File
1. Di komputer Anda, pastikan semua file ShopinMy sudah di-ZIP (misal: `shopinmy.zip`).
2. Jangan lupa siapkan *file* database `.sql` (bisa didapat dari *phpMyAdmin* server Anda sebelumnya).

## Langkah 2: Upload ke cPanel
1. Login ke cPanel Anda.
2. Buka menu **File Manager**.
3. Di root direktori Anda (biasanya `/home/username`), **BUAT FOLDER BARU** bernama `shopinmy_core` (sejajar dengan `public_html`).
4. Masuk ke dalam folder `shopinmy_core`, lalu **Upload** file `shopinmy.zip` tadi ke dalamnya.
5. Setelah selesai, **Extract** file zip tersebut di dalam `shopinmy_core`.

## Langkah 3: Setting public_html
1. Masuk ke folder `shopinmy_core/public`.
2. **Pilih semua file** yang ada di dalam folder `public` tersebut, lalu pilih opsi **Move** (Pindahkan).
3. Pindahkan semuanya ke folder utama `/home/username/public_html`.
4. Sekarang, masuk ke folder `public_html`, lalu cari file `index.php`.
5. Klik Kanan $\rightarrow$ **Edit** file `index.php`.
6. Cari dua baris kode ini:
   ```php
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   ```
   **GANTI** menjadi:
   ```php
   require __DIR__.'/../shopinmy_core/vendor/autoload.php';
   $app = require_once __DIR__.'/../shopinmy_core/bootstrap/app.php';
   ```
7. Klik **Save Changes**.

## Langkah 4: Upload Database
1. Kembali ke Dashboard cPanel, buka menu **MySQL Databases**.
2. Buat database baru (contoh: `shopinmy_db`) dan buat *user* baru, lalu sambungkan *user* tersebut ke database dengan memberikan *All Privileges*.
3. Buka menu **phpMyAdmin** di cPanel.
4. Pilih database `shopinmy_db` yang baru dibuat.
5. Klik tab **Import**, lalu pilih file `.sql` Anda dan klik *Go*.

## Langkah 5: Setting .env
1. Buka File Manager, masuk ke folder `shopinmy_core`.
2. Klik tombol **Settings** (pojok kanan atas), centang *Show Hidden Files (dotfiles)*, lalu *Save*.
3. Cari file `.env`, klik kanan $\rightarrow$ **Edit**.
4. Ubah pengaturan database sesuai yang Anda buat di Langkah 4:
   ```env
   DB_DATABASE=username_shopinmy_db
   DB_USERNAME=username_shopinmy_user
   DB_PASSWORD=password_yang_anda_buat
   ```
5. Sesuaikan juga `APP_URL` menjadi nama domain Anda (contoh: `https://domainanda.com`).
6. Klik **Save Changes**.

## Langkah 6: Mengaktifkan Gambar (Symlink)
1. Buka browser Anda.
2. Ketikkan alamat berikut: `https://domainanda.com/link_storage.php`
3. Jika muncul tulisan *Symlink process successfully completed*, artinya folder gambar sudah berhasil tersambung.
4. Selesai! Website ShopinMy Anda sudah online dengan aman.

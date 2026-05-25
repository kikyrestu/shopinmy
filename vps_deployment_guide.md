# Panduan Cepat Deploy ke VPS (Docker)

Panduan ini didesain sesederhana mungkin untuk Klien Anda yang tidak mengerti koding. Seluruh proses sudah diotomatisasi menggunakan *script installer*.

## Tahap 1: Persiapan Domain & Server
1. Beli layanan VPS (Rekomendasi: Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS).
2. Dapatkan **Alamat IP Public** dari VPS tersebut (contoh: `192.168.1.100`).
3. Buka pengaturan DNS pada panel Domain Anda, buat **A Record** baru, lalu masukkan Alamat IP VPS tersebut. Tunggu sekitar 5-10 menit agar DNS menyebar (*Propagasi*).

## Tahap 2: Menjalankan Auto-Installer
1. Hubungkan komputer Anda ke VPS menggunakan Terminal, Putty, atau Termius (Login sebagai `root`).
2. Setelah masuk ke layar hitam, **Salin (Copy) dan Tempel (Paste)** kode di bawah ini lalu tekan Enter:
   ```bash
   bash <(curl -s https://raw.githubusercontent.com/kikyrestu/shopinmy/main/install.sh)
   ```
3. Script akan meminta Anda memasukkan **Nama Domain**, **Email** (untuk sertifikat SSL), dan **Password Database** yang Anda inginkan.
4. Duduk manis! Script akan otomatis mengunduh Docker, menata sistem, dan mengamankan website Anda dengan gembok hijau (HTTPS).

## Tahap 3: Import Database (Bila Ada)
Jika Anda memiliki data dari server lama (file `.sql`), upload file tersebut ke server VPS Anda (misalnya menggunakan FileZilla/SFTP ke folder `/var/www/shopinmy`), lalu jalankan perintah ini di layar hitam:
```bash
docker exec -i shopinmy_db mysql -u shopinmy_user -p'PASSWORD_DATABASE_ANDA_TADI' shopinmy < nama_file_database_lama.sql
```

Selesai! Website Anda sudah online dan siap digunakan di VPS baru.

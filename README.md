<h1 align="center">🚀 Panduan Lengkap Instalasi ShopinMy (Dari Nol Sampai Online)</h1>

<p align="center">
Panduan ini dirancang khusus untuk pemula. Ikuti langkah-langkah di bawah ini secara perlahan, dan website toko online Anda akan siap digunakan dalam hitungan menit!
</p>

---

## 📌 Tahap 1: Persiapan Domain (Wajib)
Sebelum menyentuh server, pastikan domain Anda sudah diarahkan ke server VPS.
1. Buka panel pengelolaan Domain Anda (seperti Niagahoster, Hostinger, Idwebhost, atau Cloudflare).
2. Cari menu **DNS Management** atau **Pengaturan DNS**.
3. Buat catatan baru dengan tipe **A Record**.
4. Isi kolom `Name` dengan `@` (untuk domain utama) atau nama subdomain (misal: `toko1`).
5. Isi kolom `IPv4 address` atau `Points to` dengan **IP Server VPS Anda**.
6. Simpan. *(Catatan: Perubahan DNS kadang butuh waktu beberapa menit hingga 1 jam untuk menyebar ke seluruh dunia).*

---

## 💻 Tahap 2: Cara Masuk ke VPS (Khusus Pemula)
Anda harus masuk ke dalam server VPS Anda untuk menjalankan perintah instalasi.

1. Buka aplikasi Terminal di komputer Anda:
   - **Pengguna Windows**: Buka aplikasi `Command Prompt` (CMD) atau `PowerShell`.
   - **Pengguna Mac/Linux**: Buka aplikasi `Terminal`.
2. Ketik perintah di bawah ini, lalu tekan **Enter**:
   ```bash
   ssh root@IP_VPS_ANDA
   ```
   *(Contoh: `ssh root@217.216.33.192`)*

3. Jika muncul pertanyaan `Are you sure you want to continue connecting (yes/no/[fingerprint])?`, ketik **yes** lalu tekan Enter.

### ⚠️ PERHATIAN SANGAT PENTING SAAT MENGISI PASSWORD:
Sistem akan meminta Anda memasukkan password VPS Anda.
Ketika Anda mulai mengetik password, **huruf, angka, atau tanda bintang (***) TIDAK AKAN MUNCUL di layar!** 
Layar akan terlihat seperti diam / nge-lag. JANGAN PANIK! Ini **BUKAN ERROR**, melainkan fitur keamanan tingkat tinggi bawaan sistem operasi Linux agar tidak ada orang di sebelah Anda yang bisa menebak panjang password Anda. 

**Cara yang benar:** Ketik saja password Anda dengan percaya diri sampai selesai (meskipun tidak terlihat ada yang berubah di layar), lalu tekan **Enter**.

---

## 🚀 Tahap 3: Menjalankan Auto-Installer (Sihir Dimulai)
Setelah berhasil masuk (biasanya ditandai dengan tulisan hijau seperti `root@vmi...:~#`), Anda hanya perlu **Copy dan Paste** satu baris kode ajaib ini ke terminal, lalu tekan Enter:

```bash
bash <(curl -s https://raw.githubusercontent.com/kikyrestu/shopinmy/main/install.sh)
```

---

## ❓ Tahap 4: Menjawab Pertanyaan Instalasi
Sistem akan secara otomatis mendownload semua kebutuhan server (Nginx, Docker, Certbot) dan menanyakan 4 hal mudah:

1. **🛒 KODE TOKO**: Nama unik atau ID untuk toko Anda. Gunakan huruf kecil tanpa spasi (misal: `toko1`, `tokosepatuku`).
2. **🌐 NAMA DOMAIN**: Domain atau Subdomain yang sudah Anda setting di Tahap 1 (misal: `toko1.com` atau `toko1.shopinmy.com`).
3. **📧 EMAIL**: Alamat email aktif Anda. Email ini dibutuhkan oleh *Let's Encrypt* untuk memberikan Anda sertifikat Gembok Hijau (HTTPS/SSL) secara gratis dan resmi.
4. **🔑 PASSWORD DATABASE**: Buat password yang kuat untuk mengamankan *database* toko ini.

Setelah menjawab 4 pertanyaan tersebut, Anda tinggal **Duduk Manis dan Ngopi ☕**.
Script akan bekerja menginstall aplikasi, membuat database, menyambungkan jaringan, dan mengaktifkan sertifikat gembok hijau HTTPS!

---

## 🛠️ Fitur Ekstra (Untuk Admin Server)

### 🔄 Cara Mengupdate Toko ke Versi Terbaru
Jika ada pembaruan kode, fitur baru, atau perbaikan *bug* dari pusat (GitHub), Anda tidak perlu menginstall ulang! Cukup masuk ke VPS Anda dan jalankan:
```bash
bash <(curl -s https://raw.githubusercontent.com/kikyrestu/shopinmy/main/update.sh)
```
Toko Anda akan otomatis diperbarui dalam hitungan detik tanpa mematikan website (Zero Downtime).

### 🗑️ Cara Menghapus Toko (Uninstall Permanen)
Jika masa sewa klien Anda habis atau Anda ingin membersihkan *server* dari toko tertentu, jalankan perintah pemusnah ini:
```bash
bash <(curl -s https://raw.githubusercontent.com/kikyrestu/shopinmy/main/uninstall.sh)
```
Script ini akan membersihkan semua *file*, *database*, *docker container*, dan *setting* Nginx yang berhubungan dengan toko tersebut sampai tak bersisa, mengembalikan memori dan kapasitas penyimpanan VPS Anda.

---
<p align="center">
Dibuat dengan ❤️ untuk sistem SaaS ShopinMy Multi-Tenant.
</p>

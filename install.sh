#!/bin/bash

# ==============================================================================
# ShopinMy VPS Auto Installer (Docker)
# ==============================================================================

# 1. Cek Root Privilege
if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: Tolong jalankan script ini sebagai root (Gunakan 'sudo bash install.sh')"
  exit 1
fi

echo -e "\n======================================================="
echo "🚀 SELAMAT DATANG DI SHOPINMY AUTO-INSTALLER (VPS) 🚀"
echo "======================================================="

# 2. Minta Input dari User
read -p "🌐 Masukkan Nama Domain Anda (contoh: tokoku.com): " DOMAIN_NAME
read -p "📧 Masukkan Email Anda (untuk SSL/HTTPS Let's Encrypt): " ADMIN_EMAIL
read -p "🔑 Masukkan Password Database yang diinginkan: " DB_PASS

if [ -z "$DOMAIN_NAME" ] || [ -z "$ADMIN_EMAIL" ] || [ -z "$DB_PASS" ]; then
    echo "❌ Error: Semua isian (Domain, Email, Password) wajib diisi!"
    exit 1
fi

echo -e "\n⏳ Memulai proses instalasi otomatis. Silakan tunggu santai..."

# 3. Update System & Install Dependensi Dasar
echo "📦 Mengupdate sistem dan menginstal dependensi dasar..."
apt-get update -y
apt-get install -y curl git unzip wget

# 4. Install Docker jika belum ada
if ! command -v docker &> /dev/null; then
    echo "🐳 Menginstal Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
else
    echo "✅ Docker sudah terinstal."
fi

# 5. Clone Repository
APP_DIR="/var/www/shopinmy"
echo "📥 Mengunduh kode aplikasi ke $APP_DIR..."

if [ -d "$APP_DIR" ]; then
    echo "⚠️ Folder $APP_DIR sudah ada. Membackup folder lama ke $APP_DIR-backup..."
    mv "$APP_DIR" "${APP_DIR}-backup-$(date +%s)"
fi

mkdir -p /var/www
git clone https://github.com/kikyrestu/shopinmy.git "$APP_DIR"
cd "$APP_DIR"

# 6. Setup .env
echo "⚙️ Menyiapkan file .env..."
cp .env.example .env

# Generate string acak untuk APP_KEY
APP_KEY="base64:$(openssl rand -base64 32)"

# Manipulasi file .env
sed -i "s|^APP_NAME=.*|APP_NAME=ShopinMy|" .env
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN_NAME}|" .env

sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=ecommerce_db|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=3306|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=shopinmy|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=shopinmy_user|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env

# 7. Setup Nginx Configuration untuk Domain (Non-SSL sementara)
echo "🌐 Mengatur konfigurasi Nginx..."
cp docker/nginx/production.conf docker/nginx/default.conf
sed -i "s/yourdomain.com/${DOMAIN_NAME}/g" docker/nginx/default.conf

# 8. Start Docker Containers
echo "🚀 Mengaktifkan layanan Docker..."
export DB_PASSWORD=$DB_PASS
docker compose up -d --build

# 9. Install dependensi Composer & NPM di dalam container app
echo "📦 Menginstall modul sistem (Composer & NPM)..."
docker compose exec -T ecommerce_app composer install --no-dev --optimize-autoloader
docker compose exec -T ecommerce_app npm install
docker compose exec -T ecommerce_app npm run build

# 10. Persiapkan Database & Symlink
echo "🗃️ Melakukan migrasi database dan menyambungkan gambar..."
docker compose exec -T ecommerce_app php artisan migrate --force
docker compose exec -T ecommerce_app php artisan storage:link

# 11. Optimasi Cache Laravel
echo "⚡ Mengoptimalkan sistem cache..."
docker compose exec -T ecommerce_app php artisan optimize:clear
docker compose exec -T ecommerce_app php artisan config:cache
docker compose exec -T ecommerce_app php artisan route:cache
docker compose exec -T ecommerce_app php artisan view:cache

# 12. Setup Auto-SSL Let's Encrypt dengan Certbot
echo "🔒 Memasang Sertifikat SSL (HTTPS Gembok Hijau)..."
apt-get install -y certbot python3-certbot-nginx

# Menggunakan certbot webroot untuk verifikasi tanpa mematikan Nginx
docker compose exec -T ecommerce_web mkdir -p /var/www/html/public/.well-known/acme-challenge
certbot certonly --webroot -w "$APP_DIR/public" -d "$DOMAIN_NAME" --email "$ADMIN_EMAIL" --agree-tos --non-interactive

# Jika sertifikat berhasil dibuat, update Nginx config untuk support HTTPS
if [ -d "/etc/letsencrypt/live/${DOMAIN_NAME}" ]; then
    echo "✅ SSL Berhasil! Mengupdate konfigurasi Nginx untuk menggunakan SSL..."
    
    cat > docker/nginx/default.conf <<EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ${DOMAIN_NAME};
    root /var/www/html/public;

    ssl_certificate /etc/letsencrypt/live/${DOMAIN_NAME}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN_NAME}/privkey.pem;

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass ecommerce_app:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Supaya docker nginx bisa baca /etc/letsencrypt, kita perlu binding volume di docker-compose
    # Kita modif file docker-compose.yml menggunakan sed
    sed -i '/- .\/docker\/nginx\/default.conf:\/etc\/nginx\/conf.d\/default.conf/a \      - /etc/letsencrypt:/etc/letsencrypt:ro' docker-compose.yml
    
    # Restart nginx container
    docker compose up -d ecommerce_web
else
    echo "⚠️ Peringatan: Pemasangan SSL gagal (Mungkin DNS domain belum diarahkan ke IP VPS ini)."
fi

echo -e "\n======================================================="
echo "🎉 INSTALASI SELESAI DENGAN SUKSES! 🎉"
echo "======================================================="
echo "Website Anda sekarang sudah live di: https://${DOMAIN_NAME}"
echo ""
echo "Lokasi Folder Instalasi: ${APP_DIR}"
echo "Untuk mengimpor database lama (jika ada), silakan buka phpMyAdmin atau import via terminal:"
echo "👉 docker exec -i shopinmy_db mysql -u shopinmy_user -p'${DB_PASS}' shopinmy < shopinmy_db.sql"
echo "======================================================="

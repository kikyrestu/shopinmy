#!/bin/bash

# ==============================================================================
# ShopinMy MULTI-TENANT VPS Auto Installer (Nginx Proxy + Let's Encrypt)
# ==============================================================================

# 1. Cek Root Privilege
if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: Tolong jalankan script ini sebagai root (Gunakan 'sudo bash install.sh')"
  exit 1
fi

echo -e "\n======================================================="
echo "🚀 SELAMAT DATANG DI SHOPINMY MULTI-STORE INSTALLER 🚀"
echo "======================================================="

# 2. Minta Input dari User
read -p "🛒 Masukkan KODE TOKO (tanpa spasi, misal: toko1, bajuku): " STORE_CODE
read -p "🌐 Masukkan NAMA DOMAIN (misal: toko1.com): " DOMAIN_NAME
read -p "📧 Masukkan EMAIL (untuk SSL/HTTPS Let's Encrypt): " ADMIN_EMAIL
read -p "🔑 Masukkan PASSWORD DATABASE yang diinginkan: " DB_PASS

if [ -z "$STORE_CODE" ] || [ -z "$DOMAIN_NAME" ] || [ -z "$ADMIN_EMAIL" ] || [ -z "$DB_PASS" ]; then
    echo "❌ Error: Semua isian (Kode Toko, Domain, Email, Password) wajib diisi!"
    exit 1
fi

echo -e "\n⏳ Memulai proses instalasi otomatis. Silakan tunggu..."

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

# 5. Menyiapkan Global Nginx Reverse Proxy (Jalan 1x saja di server)
PROXY_DIR="/var/www/nginx-proxy"
if [ ! -d "$PROXY_DIR" ]; then
    echo "🚦 Menyiapkan Global Nginx Reverse Proxy (Polisi Lalu Lintas)..."
    mkdir -p "$PROXY_DIR"
    
    # Buat docker network global
    docker network create nginx-proxy || true

    cat > "$PROXY_DIR/docker-compose.yml" <<EOF
version: '3'
services:
  nginx-proxy:
    image: nginxproxy/nginx-proxy:alpine
    container_name: nginx-proxy
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - certs:/etc/nginx/certs
      - vhost:/etc/nginx/vhost.d
      - html:/usr/share/nginx/html
    networks:
      - default
    restart: always

  acme-companion:
    image: nginxproxy/acme-companion
    container_name: nginx-proxy-acme
    environment:
      - DEFAULT_EMAIL=${ADMIN_EMAIL}
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - acme:/etc/acme.sh
      - certs:/etc/nginx/certs
      - vhost:/etc/nginx/vhost.d
      - html:/usr/share/nginx/html
    depends_on:
      - nginx-proxy
    networks:
      - default
    restart: always

volumes:
  certs:
  vhost:
  html:
  acme:

networks:
  default:
    external:
      name: nginx-proxy
EOF
    cd "$PROXY_DIR"
    docker compose up -d
else
    echo "✅ Global Nginx Proxy sudah berjalan."
fi

# 6. Clone Repository untuk Toko Baru
APP_DIR="/var/www/stores/$STORE_CODE"
echo "📥 Menyiapkan folder untuk toko $STORE_CODE di $APP_DIR..."

if [ -d "$APP_DIR" ]; then
    echo "⚠️ Folder $APP_DIR sudah ada. Membackup folder lama..."
    mv "$APP_DIR" "${APP_DIR}-backup-$(date +%s)"
fi

mkdir -p /var/www/stores
git clone https://github.com/kikyrestu/shopinmy.git "$APP_DIR"
cd "$APP_DIR"

# 7. Setup .env
echo "⚙️ Menyiapkan konfigurasi .env..."
cp .env.example .env

# Generate string acak untuk APP_KEY
APP_KEY="base64:$(openssl rand -base64 32)"

# Menyisipkan variabel ekstra yang dipakai oleh docker-compose.prod.yml
echo "" >> .env
echo "STORE_CODE=${STORE_CODE}" >> .env
echo "DOMAIN_NAME=${DOMAIN_NAME}" >> .env
echo "ADMIN_EMAIL=${ADMIN_EMAIL}" >> .env

sed -i "s|^APP_NAME=.*|APP_NAME=ShopinMy_${STORE_CODE}|" .env
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

# 8. Start Docker Containers khusus untuk Toko ini
echo "🚀 Menyalakan kontainer toko ${STORE_CODE}..."
# Kita jalankan pakai docker-compose.prod.yml yang dirancang khusus untuk Multi-Tenant Reverse Proxy
docker compose -f docker-compose.prod.yml up -d --build

# 9. Install dependensi Composer & NPM di dalam container app
echo "📦 Menginstall pustaka PHP & Node..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app composer install --no-dev --optimize-autoloader
docker compose -f docker-compose.prod.yml exec -T ecommerce_app npm install
docker compose -f docker-compose.prod.yml exec -T ecommerce_app npm run build

# 10. Persiapkan Database & Symlink
echo "🗃️ Melakukan migrasi database dan menyambungkan gambar..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan storage:link

# 11. Optimasi Cache Laravel
echo "⚡ Mengoptimalkan sistem cache..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan view:cache

# 12. Mendaftarkan Cron Job ke Sistem VPS
echo "⏱️ Memasang jadwal Cron Job otomatis..."
CRON_CMD="* * * * * cd $APP_DIR && docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "$APP_DIR"; echo "$CRON_CMD") | crontab -

echo -e "\n======================================================="
echo "🎉 INSTALASI TOKO '${STORE_CODE}' SELESAI! 🎉"
echo "======================================================="
echo "Website Anda sedang diterbitkan di: https://${DOMAIN_NAME}"
echo "Sertifikat HTTPS (Gembok Hijau) sedang diterbitkan secara otomatis di background."
echo "Proses penerbitan SSL memakan waktu sekitar 1-2 menit. Jika masih error, tunggu sejenak dan refresh."
echo ""
echo "Lokasi Folder Instalasi: ${APP_DIR}"
echo "Untuk mengimpor database lama (jika ada), silakan jalan perintah ini:"
echo "👉 docker exec -i ${STORE_CODE}_db mysql -u shopinmy_user -p'${DB_PASS}' shopinmy < database_lama.sql"
echo "======================================================="

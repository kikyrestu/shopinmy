#!/bin/bash

# ==============================================================================
# ShopinMy MULTI-TENANT VPS Auto Installer (Native Nginx + Dockerized App)
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
read -p "🌐 Masukkan NAMA DOMAIN atau SUBDOMAIN (misal: toko1.com atau cabang.toko1.com): " DOMAIN_NAME
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
apt-get install -y curl git unzip wget nginx python3-certbot-nginx

# 4. Install Docker jika belum ada
if ! command -v docker &> /dev/null; then
    echo "🐳 Menginstal Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
else
    echo "✅ Docker sudah terinstal."
fi

# 5. Mencari Port Kosong untuk Kontainer Web Toko Ini
echo "🔍 Mencari port lokal yang tersedia..."
APP_PORT=8000
while ss -tuln | grep -q ":$APP_PORT "; do
    APP_PORT=$((APP_PORT+1))
done
echo "✅ Mendapatkan port lokal: $APP_PORT"

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

# Buka gembok agar Apache dalam kontainer bisa menulis file cache/session
chmod -R 777 storage bootstrap/cache

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
echo "APP_PORT=${APP_PORT}" >> .env

sed -i "s|^APP_NAME=.*|APP_NAME=ShopinMy_${STORE_CODE}|" .env
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN_NAME}|" .env
sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|" .env

# Hapus pengaturan DB lama (termasuk yang di-comment)
sed -i '/^DB_/d' .env
sed -i '/^# DB_/d' .env

# Tambahkan pengaturan DB baru
cat <<EOT >> .env
DB_CONNECTION=mysql
DB_HOST=ecommerce_db
DB_PORT=3306
DB_DATABASE=shopinmy
DB_USERNAME=shopinmy_user
DB_PASSWORD=${DB_PASS}
EOT

# 8. Start Docker Containers khusus untuk Toko ini
echo "🚀 Menyalakan kontainer toko ${STORE_CODE}..."
# Kita jalankan pakai docker-compose.prod.yml
docker compose -f docker-compose.prod.yml up -d --build

# 9. Install dependensi Composer di dalam container app
echo "📦 Menginstall pustaka PHP..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app composer install --no-dev --optimize-autoloader || { echo "❌ ERROR: Composer install gagal!"; exit 1; }

# 10. Persiapkan Database & Symlink
echo "🗃️ Menjalankan migrasi database (Menunggu MySQL siap, maksimal 90 detik)..."
MIGRATION_SUCCESS=false
for i in {1..30}; do
    if docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan migrate --force; then
        MIGRATION_SUCCESS=true
        break
    fi
    echo "⚠️ Database sedang proses booting... (Mencoba lagi dalam 3 detik - Percobaan $i/30)"
    sleep 3
done

if [ "$MIGRATION_SUCCESS" = false ]; then
    echo "❌ ERROR: Migrasi database gagal setelah mencoba selama 90 detik! Pastikan volume database lama sudah dihapus jika ini install ulang."
    exit 1
fi
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan storage:link

# 11. Optimasi Cache Laravel
echo "⚡ Mengoptimalkan sistem cache..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan optimize:clear || true
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan config:cache || { echo "❌ ERROR: Config cache gagal!"; exit 1; }
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan route:cache || true
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan view:cache || true

# 12. Mengkonfigurasi Native Nginx & Certbot SSL
echo "🌐 Mengatur Virtual Host Nginx Asli..."
NGINX_CONF="/etc/nginx/sites-available/${STORE_CODE}.conf"
cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME};

    location / {
        proxy_pass http://127.0.0.1:${APP_PORT};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

# Aktifkan konfigurasi
ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${STORE_CODE}.conf"
systemctl restart nginx || { echo "❌ ERROR: Gagal me-restart Nginx!"; exit 1; }

echo "🔒 Mengaktifkan SSL / HTTPS dengan Certbot..."
certbot --nginx -d "${DOMAIN_NAME}" --non-interactive --agree-tos -m "${ADMIN_EMAIL}" --redirect || { echo "❌ ERROR: Gagal menerbitkan SSL Certbot! Pastikan DNS sudah mengarah ke IP Server ini."; }

# 13. Mendaftarkan Cron Job ke Sistem VPS
echo "⏱️ Memasang jadwal Cron Job otomatis..."
CRON_CMD="* * * * * cd $APP_DIR && docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "$APP_DIR"; echo "$CRON_CMD") | crontab -

echo -e "\n======================================================="
echo "🎉 INSTALASI TOKO '${STORE_CODE}' SELESAI! 🎉"
echo "======================================================="
echo "Website Anda sudah live di: https://${DOMAIN_NAME}"
echo ""
echo "Lokasi Folder Instalasi: ${APP_DIR}"
echo "Untuk mengimpor database lama (jika ada), silakan jalan perintah ini:"
echo "👉 docker exec -i ${STORE_CODE}_db mysql -u shopinmy_user -p'${DB_PASS}' shopinmy < database_lama.sql"
echo "======================================================="

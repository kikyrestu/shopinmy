#!/bin/bash

# ==============================================================================
# ShopinMy MULTI-TENANT Updater Script
# ==============================================================================

if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: Tolong jalankan script ini sebagai root (sudo bash update.sh)"
  exit 1
fi

echo -e "\n======================================================="
echo "🔄 SHOPINMY AUTO-UPDATER 🔄"
echo "======================================================="

read -p "🛒 Masukkan KODE TOKO yang ingin di-update (misal: toko1): " STORE_CODE

APP_DIR="/var/www/stores/$STORE_CODE"

if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: Toko dengan kode '$STORE_CODE' tidak ditemukan di $APP_DIR!"
    exit 1
fi

echo -e "\n⏳ Mengupdate $STORE_CODE... Web Anda tidak akan mati selama proses ini."

cd "$APP_DIR"

# 1. Tarik pembaruan kode terbaru dari GitHub
echo "🔄 Mengambil update terbaru dari repository..."
git reset --hard || { echo "❌ ERROR: Git reset gagal!"; exit 1; }
git pull origin main || { echo "❌ ERROR: Git pull gagal!"; exit 1; }

# Pastikan permission folder tetap aman setelah update
chmod -R 777 storage bootstrap/cache

# 2. Update dependensi (Jika ada tambahan library)
echo "📦 Mengupdate dependensi Composer..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app composer install --no-dev --optimize-autoloader || { echo "❌ ERROR: Composer install gagal!"; exit 1; }

# 3. Jalankan Migrasi Database (Jika ada tabel baru)
echo "🗃️ Menjalankan migrasi database..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan migrate --force || { echo "❌ ERROR: Migrasi database gagal!"; exit 1; }

echo "🌱 Menjalankan Seeder Database secara terpisah..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan db:seed --force || echo "⚠️ Peringatan: Seeder gagal dijalankan, namun migrasi berhasil."

echo "⚡ Menyegarkan semua Cache Laravel..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan view:cache

# Restart antrean pekerjaan di background agar mendeteksi kode baru
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan queue:restart

echo -e "\n======================================================="
echo "🎉 PROSES UPDATE TOKO '${STORE_CODE}' SELESAI! 🎉"
echo "======================================================="

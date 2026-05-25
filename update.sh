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

echo "📥 Menarik kode terbaru dari GitHub..."
git pull origin main

echo "📦 Mengupdate pustaka PHP (Composer)..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app composer install --no-dev --optimize-autoloader

echo "📦 Mengupdate pustaka Node (NPM) & Rebuild Assets..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app npm install
docker compose -f docker-compose.prod.yml exec -T ecommerce_app npm run build

echo "🗃️ Menjalankan migrasi database (jika ada struktur tabel baru)..."
docker compose -f docker-compose.prod.yml exec -T ecommerce_app php artisan migrate --force

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

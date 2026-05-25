#!/bin/bash

# ==============================================================================
# ShopinMy MULTI-TENANT Uninstaller
# ==============================================================================

if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: Tolong jalankan script ini sebagai root (sudo bash uninstall.sh)"
  exit 1
fi

echo -e "\n======================================================="
echo "🗑️ SHOPINMY STORE UNINSTALLER 🗑️"
echo "======================================================="

read -p "⚠️ Masukkan KODE TOKO yang ingin DIHAPUS PERMANEN (misal: toko1): " STORE_CODE

APP_DIR="/var/www/stores/$STORE_CODE"

if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: Toko dengan kode '$STORE_CODE' tidak ditemukan di $APP_DIR!"
    exit 1
fi

echo "⚠️ PERINGATAN BUKAN MAIN: Semua data, database, dan file untuk toko '$STORE_CODE' akan dimusnahkan."
read -p "Apakah Anda YAKIN 100%? (Ketik 'YAKIN' untuk lanjut): " CONFIRM

if [ "$CONFIRM" != "YAKIN" ]; then
    echo "❌ Penghapusan dibatalkan demi keamanan."
    exit 1
fi

echo -e "\n🧹 Memulai proses pemusnahan toko $STORE_CODE..."

cd "$APP_DIR"

echo "🛑 Mematikan mesin kontainer dan menghapus database volume..."
docker compose -f docker-compose.prod.yml down -v

echo "🗑️ Menghapus jadwal Cron Job milik toko ini..."
crontab -l | grep -v "/var/www/stores/$STORE_CODE/" | crontab -

cd /var/www/stores
echo "🔥 Membakar folder aplikasi..."
rm -rf "$STORE_CODE"

echo -e "\n======================================================="
echo "✅ TOKO '${STORE_CODE}' BERHASIL DIHAPUS BERSIH DARI SERVER!"
echo "======================================================="

<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Group: general
            ['key' => 'site_name', 'value' => 'CommBuildy', 'group' => 'general', 'label' => 'Nama Toko', 'description' => 'Nama toko yang tampil di header dan email', 'is_encrypted' => false],
            ['key' => 'site_logo', 'value' => null, 'group' => 'general', 'label' => 'Logo Toko', 'description' => 'Upload logo toko (PNG/SVG)', 'is_encrypted' => false],
            ['key' => 'site_favicon', 'value' => null, 'group' => 'general', 'label' => 'Favicon', 'description' => 'Upload favicon (ICO/PNG)', 'is_encrypted' => false],
            ['key' => 'site_email', 'value' => 'admin@commbuildy.com', 'group' => 'general', 'label' => 'Email Toko', 'description' => 'Email utama toko untuk komunikasi', 'is_encrypted' => false],
            ['key' => 'site_phone', 'value' => null, 'group' => 'general', 'label' => 'Nomor HP Toko', 'description' => 'Nomor WhatsApp / HP toko', 'is_encrypted' => false],
            ['key' => 'site_address', 'value' => null, 'group' => 'general', 'label' => 'Alamat Toko', 'description' => 'Alamat fisik toko (origin pengiriman)', 'is_encrypted' => false],
            ['key' => 'site_language', 'value' => 'en', 'group' => 'general', 'label' => 'Bahasa Default', 'description' => 'my = Bahasa Malaysia, en = English', 'is_encrypted' => false],

            // Group: sst
            ['key' => 'sst_enabled', 'value' => false, 'group' => 'sst', 'label' => 'Aktifkan SST', 'description' => 'Aktifkan Sales and Services Tax Malaysia', 'is_encrypted' => false],
            ['key' => 'sst_rate', 'value' => '8', 'group' => 'sst', 'label' => 'Rate SST (%)', 'description' => 'Default: 8%', 'is_encrypted' => false],
            ['key' => 'sst_label', 'value' => 'SST (8%)', 'group' => 'sst', 'label' => 'Label Tampil', 'description' => 'Label SST yang tampil di invoice', 'is_encrypted' => false],

            // Group: payment
            ['key' => 'billplz_enabled', 'value' => false, 'group' => 'payment', 'label' => 'Aktifkan Billplz', 'description' => 'Payment Gateway FPX', 'is_encrypted' => false],
            ['key' => 'billplz_api_key', 'value' => null, 'group' => 'payment', 'label' => 'Billplz API Key', 'description' => null, 'is_encrypted' => true],
            ['key' => 'billplz_collection_id', 'value' => null, 'group' => 'payment', 'label' => 'Billplz Collection ID', 'description' => null, 'is_encrypted' => true],
            ['key' => 'billplz_x_signature', 'value' => null, 'group' => 'payment', 'label' => 'Billplz X-Signature', 'description' => null, 'is_encrypted' => true],
            ['key' => 'billplz_sandbox', 'value' => true, 'group' => 'payment', 'label' => 'Billplz Sandbox Mode', 'description' => 'Aktifkan mode sandbox untuk testing', 'is_encrypted' => false],
            ['key' => 'stripe_enabled', 'value' => false, 'group' => 'payment', 'label' => 'Aktifkan Stripe', 'description' => 'Kartu Kredit / Debit', 'is_encrypted' => false],
            ['key' => 'stripe_publishable_key', 'value' => null, 'group' => 'payment', 'label' => 'Stripe Publishable Key', 'description' => null, 'is_encrypted' => false],
            ['key' => 'stripe_secret_key', 'value' => null, 'group' => 'payment', 'label' => 'Stripe Secret Key', 'description' => null, 'is_encrypted' => true],
            ['key' => 'stripe_webhook_secret', 'value' => null, 'group' => 'payment', 'label' => 'Stripe Webhook Secret', 'description' => null, 'is_encrypted' => true],
            ['key' => 'stripe_sandbox', 'value' => true, 'group' => 'payment', 'label' => 'Stripe Test Mode', 'description' => 'Aktifkan mode test untuk Stripe', 'is_encrypted' => false],
            ['key' => 'cod_enabled', 'value' => false, 'group' => 'payment', 'label' => 'Aktifkan COD', 'description' => 'Bayar di tempat saat kurir tiba', 'is_encrypted' => false],
            ['key' => 'manual_transfer_enabled', 'value' => true, 'group' => 'payment', 'label' => 'Aktifkan Manual Transfer', 'description' => 'Customer transfer ke rekening toko', 'is_encrypted' => false],
            ['key' => 'manual_transfer_deadline_hours', 'value' => '24', 'group' => 'payment', 'label' => 'Batas Waktu Upload Bukti (jam)', 'description' => 'Order otomatis batal jika tidak upload bukti', 'is_encrypted' => false],

            // Group: shipping
            ['key' => 'myparcel_api_key', 'value' => null, 'group' => 'shipping', 'label' => 'MyParcel API Key', 'description' => 'API Key dari MyParcel Asia dashboard', 'is_encrypted' => true],
            ['key' => 'myparcel_api_secret', 'value' => null, 'group' => 'shipping', 'label' => 'MyParcel API Secret', 'description' => 'API Secret dari MyParcel Asia dashboard', 'is_encrypted' => true],
            ['key' => 'myparcel_sandbox', 'value' => true, 'group' => 'shipping', 'label' => 'MyParcel Sandbox Mode', 'description' => 'Gunakan demo.myparcelasia.com untuk testing', 'is_encrypted' => false],
            ['key' => 'myparcel_default_provider', 'value' => 'poslaju', 'group' => 'shipping', 'label' => 'Default Courier', 'description' => 'poslaju, jnt, ninjavan, citylink, flash, dll', 'is_encrypted' => false],
            ['key' => 'myparcel_default_size', 'value' => 'flyers_l', 'group' => 'shipping', 'label' => 'Default Parcel Size', 'description' => 'flyers_s/m/l/xl, box, envelope_a4', 'is_encrypted' => false],
            ['key' => 'myparcel_send_method', 'value' => 'pickup', 'group' => 'shipping', 'label' => 'Send Method', 'description' => 'pickup atau dropoff', 'is_encrypted' => false],
            ['key' => 'store_postcode', 'value' => null, 'group' => 'shipping', 'label' => 'Postcode Asal Pengiriman', 'description' => 'Postcode lokasi toko', 'is_encrypted' => false],
            ['key' => 'store_state', 'value' => null, 'group' => 'shipping', 'label' => 'State Asal Pengiriman', 'description' => 'State lokasi toko di Malaysia', 'is_encrypted' => false],

            // Group: notification
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'group' => 'notification', 'label' => 'SMTP Host', 'description' => null, 'is_encrypted' => false],
            ['key' => 'mail_port', 'value' => '587', 'group' => 'notification', 'label' => 'SMTP Port', 'description' => null, 'is_encrypted' => false],
            ['key' => 'mail_username', 'value' => null, 'group' => 'notification', 'label' => 'SMTP Username', 'description' => null, 'is_encrypted' => true],
            ['key' => 'mail_password', 'value' => null, 'group' => 'notification', 'label' => 'SMTP Password', 'description' => null, 'is_encrypted' => true],
            ['key' => 'mail_from_address', 'value' => null, 'group' => 'notification', 'label' => 'From Email', 'description' => 'Alamat email pengirim', 'is_encrypted' => false],
            ['key' => 'mail_from_name', 'value' => 'CommBuildy', 'group' => 'notification', 'label' => 'From Name', 'description' => 'Nama pengirim email', 'is_encrypted' => false],
            ['key' => 'fonnte_token', 'value' => null, 'group' => 'notification', 'label' => 'Fonnte Token', 'description' => 'Token API Fonnte untuk WhatsApp', 'is_encrypted' => true],
            ['key' => 'fonnte_sender', 'value' => null, 'group' => 'notification', 'label' => 'Nomor WhatsApp Pengirim', 'description' => null, 'is_encrypted' => false],
            ['key' => 'whatsapp_enabled', 'value' => false, 'group' => 'notification', 'label' => 'Aktifkan WhatsApp Notif', 'description' => null, 'is_encrypted' => false],

            // Group: api
            ['key' => 'google_places_api_key', 'value' => null, 'group' => 'api', 'label' => 'Google Places API Key', 'description' => 'Untuk address autocomplete di checkout', 'is_encrypted' => true],
            ['key' => 'google_analytics_id', 'value' => null, 'group' => 'api', 'label' => 'Google Analytics ID', 'description' => 'G-XXXXXXXXXX', 'is_encrypted' => false],
            ['key' => 'facebook_pixel_id', 'value' => null, 'group' => 'api', 'label' => 'Facebook Pixel ID', 'description' => 'Untuk tracking ads Facebook/Meta', 'is_encrypted' => false],
            ['key' => 'crisp_website_id', 'value' => null, 'group' => 'api', 'label' => 'Crisp Website ID', 'description' => 'Untuk live chat widget', 'is_encrypted' => false],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Seeded ' . count($settings) . ' settings keys.');
    }
}

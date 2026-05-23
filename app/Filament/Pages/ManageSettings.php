<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Store Settings';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.manage-settings';

    public array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {
            $value = $setting->value;
            if ($setting->is_encrypted && $value !== null) {
                try {
                    $value = \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Exception $e) {
                    $value = ''; // Fallback if decryption fails
                }
            }
            return [$setting->key => $value];
        })->toArray();

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')->tabs([

                    Tab::make('Umum')->schema([
                        FileUpload::make('site_logo')
                            ->label('Logo Website')
                            ->image()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(500)
                            ->disk('public')
                            ->directory('settings'),
                        FileUpload::make('site_favicon')
                            ->label('Favicon (Ikon Tab)')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(512)
                            ->imageResizeTargetHeight(512)
                            ->disk('public')
                            ->directory('settings'),
                        TextInput::make('site_name')->label('Nama Toko'),
                        \Filament\Forms\Components\Textarea::make('site_description')->label('Deskripsi Toko (Untuk SEO Google)')->rows(3),
                        TextInput::make('site_email')->label('Email Toko')->email(),
                        TextInput::make('site_phone')->label('No. HP'),
                        TextInput::make('site_address')->label('Alamat Toko'),
                    ]),

                    Tab::make('SST')->schema([
                        Toggle::make('sst_enabled')->label('Aktifkan SST'),
                        TextInput::make('sst_rate')->label('Rate SST (%)')->numeric()->default(8),
                        TextInput::make('sst_label')->label('Label Tampil')->default('SST (8%)'),
                    ]),

                    Tab::make('Payment')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Toggle::make('manual_transfer_enabled')->label('Aktifkan Manual Transfer'),
                            Toggle::make('cod_enabled')->label('Aktifkan COD'),
                            TextInput::make('manual_transfer_deadline_hours')->label('Batas Waktu Upload Bukti (jam)')->numeric(),

                            Section::make('Billplz (FPX)')
                                ->schema([
                                    Toggle::make('billplz_enabled')->label('Aktifkan Billplz (FPX)'),
                                    Toggle::make('billplz_sandbox')->label('Sandbox Mode'),
                                    TextInput::make('billplz_api_key')->label('API Key')->password(),
                                    TextInput::make('billplz_collection_id')->label('Collection ID')->password(),
                                    TextInput::make('billplz_x_signature')->label('X-Signature')->password(),
                                ])->columns(2),

                            Section::make('Stripe')
                                ->schema([
                                    Toggle::make('stripe_enabled')->label('Aktifkan Stripe'),
                                    Toggle::make('stripe_sandbox')->label('Test Mode'),
                                    TextInput::make('stripe_publishable_key')->label('Publishable Key'),
                                    TextInput::make('stripe_secret_key')->label('Secret Key')->password(),
                                    TextInput::make('stripe_webhook_secret')->label('Webhook Secret')->password(),
                                ])->columns(2),
                        ]),

                    Tab::make('Pengiriman')->schema([
                        Toggle::make('myparcel_sandbox')->label('Sandbox Mode'),
                        TextInput::make('myparcel_api_key')->label('MyParcel API Key')->password()->revealable(),
                        TextInput::make('myparcel_api_secret')->label('MyParcel API Secret')->password()->revealable(),
                        Select::make('myparcel_default_provider')->label('Default Courier')
                            ->options([
                                'poslaju' => 'Pos Laju',
                                'jnt' => 'J&T Express',
                                'ninjavan' => 'Ninja Van',
                                'dhl' => 'DHL eCommerce',
                                'citylink' => 'City-Link Express',
                                'gdex' => 'GDEX',
                                'skynet' => 'Skynet',
                            ])
                            ->default('poslaju'),
                        Select::make('myparcel_default_size')->label('Default Parcel Size')
                            ->options([
                                'flyers_s' => 'Flyers S',
                                'flyers_m' => 'Flyers M',
                                'flyers_l' => 'Flyers L',
                                'box_s' => 'Box S',
                                'box_m' => 'Box M',
                                'box_l' => 'Box L',
                            ])
                            ->default('flyers_l'),
                        Select::make('myparcel_send_method')->label('Send Method')
                            ->options([
                                'pickup' => 'Pickup',
                                'dropoff' => 'Drop Off',
                            ])
                            ->default('pickup'),
                        TextInput::make('store_postcode')->label('Postcode Toko'),
                        TextInput::make('store_state')->label('State Toko'),
                    ]),

                    Tab::make('Notifikasi')->schema([
                        Section::make('Email (SMTP)')->schema([
                            TextInput::make('mail_host')->label('SMTP Host'),
                            TextInput::make('mail_port')->label('Port'),
                            TextInput::make('mail_username')->label('Username'),
                            TextInput::make('mail_password')->label('Password')->password()->revealable(),
                            TextInput::make('mail_from_address')->label('From Email'),
                            TextInput::make('mail_from_name')->label('From Name'),
                        ]),
                        Section::make('WhatsApp (Fonnte)')->schema([
                            Toggle::make('whatsapp_enabled')->label('Aktifkan WhatsApp Notif'),
                            TextInput::make('fonnte_token')->label('Fonnte Token')->password()->revealable(),
                            TextInput::make('fonnte_sender')->label('Nomor Pengirim'),
                        ]),
                    ]),

                    Tab::make('API Lainnya')->schema([
                        Section::make('Google Auth (Socialite)')->schema([
                            Toggle::make('google_login_enabled')->label('Aktifkan Login dengan Google'),
                            TextInput::make('google_client_id')->label('Google Client ID'),
                            TextInput::make('google_client_secret')->label('Google Client Secret')->password()->revealable(),
                        ]),
                        Section::make('Third Party API')->schema([
                            TextInput::make('google_places_api_key')->label('Google Places API Key')->password()->revealable(),
                            TextInput::make('google_analytics_id')->label('Google Analytics ID'),
                            TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID'),
                            TextInput::make('crisp_website_id')->label('Crisp Website ID'),
                        ]),
                    ]),

                    Tab::make('Footer & Tampilan')->schema([
                        Section::make('Help Links')->schema([
                            TextInput::make('help_center_url')->label('Help Center Link')->default('#'),
                            TextInput::make('terms_conditions_url')->label('Terms & Conditions Link')->default('#'),
                            TextInput::make('privacy_policy_url')->label('Privacy Policy Link')->default('#'),
                            TextInput::make('track_order_url')->label('Track Order Link')->default('#'),
                        ])->columns(2),

                        Section::make('Newsletter')->schema([
                            TextInput::make('newsletter_title')->label('Judul Newsletter')->default('Subscribe to our Newsletter'),
                            TextInput::make('newsletter_description')->label('Deskripsi Newsletter')->default('Get the latest updates on new products and upcoming sales.'),
                        ]),

                        Section::make('Lainnya')->schema([
                            Select::make('payment_icons')->label('Logo Pembayaran')
                                ->multiple()
                                ->options([
                                    'VISA' => 'VISA',
                                    'MC' => 'MasterCard',
                                    'FPX' => 'FPX',
                                    'TnG' => 'Touch n Go',
                                    'Boost' => 'Boost',
                                    'GrabPay' => 'GrabPay',
                                    'ShopeePay' => 'ShopeePay',
                                ])
                                ->default(['VISA', 'MC', 'FPX', 'TnG', 'Boost']),
                            TextInput::make('footer_tagline')->label('Footer Tagline')->default('Made with ❤️ in Malaysia'),
                        ])->columns(2),
                    ]),
                    
                    Tab::make('Loyalty & Referral')->schema([
                        Section::make('Program Loyalty (Poin Belanja)')->schema([
                            Toggle::make('loyalty_enabled')->label('Aktifkan Loyalty Points')->default(true),
                            TextInput::make('loyalty_points_per_rm')
                                ->label('Poin per 1 RM (Contoh: 1 atau 0.5)')
                                ->numeric()
                                ->default(1),
                        ]),
                        Section::make('Program Referral')->schema([
                            Toggle::make('referral_enabled')->label('Aktifkan Referral System')->default(true),
                            TextInput::make('referral_reward_points')
                                ->label('Bonus Poin Referral')
                                ->numeric()
                                ->default(50),
                        ]),
                    ]),
                ]),
            ]);
    }

    // Encrypted keys list
    protected array $encryptedKeys = [
        'billplz_api_key', 'billplz_x_signature',
        'stripe_secret_key', 'stripe_webhook_secret',
        'myparcel_api_key', 'myparcel_api_secret',
        'mail_username', 'mail_password',
        'fonnte_token',
        'google_places_api_key', 'google_client_secret',
    ];

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $isEncrypted = in_array($key, $this->encryptedKeys);

            Setting::set($key, $value ?? '', $this->getGroupForKey($key), $isEncrypted);
        }

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    protected function getGroupForKey(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'site_') => 'general',
            str_starts_with($key, 'billplz_') => 'payment',
            str_starts_with($key, 'stripe_') => 'payment',
            str_starts_with($key, 'cod_') => 'payment',
            str_starts_with($key, 'manual_transfer_') => 'payment',
            str_starts_with($key, 'myparcel_') || str_starts_with($key, 'store_') => 'shipping',
            str_starts_with($key, 'mail_') || str_starts_with($key, 'fonnte_') || str_starts_with($key, 'whatsapp_') => 'notification',
            str_starts_with($key, 'google_') || str_starts_with($key, 'facebook_') || str_starts_with($key, 'crisp_') => 'api',
            str_starts_with($key, 'sst_') => 'sst',
            str_starts_with($key, 'loyalty_') || str_starts_with($key, 'referral_') => 'loyalty',
            default => 'general',
        };
    }
}

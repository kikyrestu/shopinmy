<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->maxLength(255)
                            ->helperText('Teks kecil di atas judul utama (Opsional).'),
                        TextInput::make('link')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('button_text')
                            ->maxLength(255)
                            ->helperText('Teks tombol, misal "Beli Sekarang" (Hanya muncul jika link diisi).'),
                        TextInput::make('sort')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->default(true),
                        Toggle::make('show_voucher')
                            ->label('Tampilkan Voucher Mengambang')
                            ->helperText('Hanya aktifkan jika tidak menghalangi desain banner di sisi kanan.')
                            ->default(false),
                    ])->columns(2),
                
                \Filament\Schemas\Components\Tabs::make('Banner Content')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Image')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('banners')
                                    ->imageEditor()
                                    ->imageResizeMode('contain')
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1080')
                                    ->helperText('Panduan Gambar: Ukuran ideal banner adalah 1920x600px atau proporsi 3:1. Format yang disarankan: JPG, PNG, WEBP dengan ukuran maksimal 2MB.'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('YouTube')
                            ->icon('heroicon-o-play-circle')
                            ->schema([
                                TextInput::make('youtube_link')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Contoh: https://www.youtube.com/watch?v=...'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('HTML')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                \Filament\Forms\Components\Textarea::make('html_content')
                                    ->helperText('Gunakan HTML tag jika ingin menyesuaikan konten. Ini akan menggantikan teks dan background bawaan.')
                                    ->columnSpanFull()
                                    ->rows(10),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

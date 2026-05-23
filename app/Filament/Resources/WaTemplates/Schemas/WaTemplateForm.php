<?php

namespace App\Filament\Resources\WaTemplates\Schemas;

use Filament\Schemas\Schema;

class WaTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Panduan Variabel & Kode Event')
                    ->description('Baca panduan ini sebelum membuat template WhatsApp.')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        \Filament\Forms\Components\ViewField::make('guide')
                            ->view('filament.components.wa-template-guide')
                            ->hiddenLabel()
                            ->dehydrated(false),
                    ]),
                    
                \Filament\Schemas\Components\Section::make('Detail Template')
                    ->description('Masukkan detail template WhatsApp.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Nama Template (Contoh: OTP Login)')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('event_code')
                            ->label('Kode Event (Sistem)')
                            ->helperText('Contek dari daftar di atas. Biarkan kosong jika ini template blast marketing.')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Pesan WhatsApp')
                            ->required()
                            ->rows(10)
                            ->helperText('Gunakan variabel yang ada di buku panduan (Contoh: Halo {name}!).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

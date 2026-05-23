<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'percentage' => 'Percentage (%)',
                        'fixed' => 'Fixed Amount (RM)',
                        'free_shipping' => 'Free Shipping',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->required(fn ($get) => $get('type') !== 'free_shipping')
                    ->hidden(fn ($get) => $get('type') === 'free_shipping')
                    ->numeric()
                    ->prefix(fn ($get) => match ($get('type')) {
                        'percentage' => '%',
                        'fixed' => 'RM',
                        default => 'RM / %',
                    }),
                TextInput::make('min_order')
                    ->numeric()
                    ->default(0)
                    ->prefix('RM'),
                TextInput::make('usage_limit')
                    ->numeric()
                    ->nullable(),
                TextInput::make('used_count')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                DateTimePicker::make('expires_at')
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}

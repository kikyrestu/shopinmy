<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Voucher Tabs')->tabs([
                    Tab::make('General Terms')->icon('heroicon-o-ticket')->schema([
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
                    ])->columns(2),

                    Tab::make('Limits & Expiry')->icon('heroicon-o-clock')->schema([
                        TextInput::make('usage_limit')
                            ->numeric()
                            ->nullable()
                            ->label('Global Usage Limit'),
                        TextInput::make('user_usage_limit')
                            ->numeric()
                            ->nullable()
                            ->label('Usage Limit per User')
                            ->helperText('Leave empty for unlimited usage per user.'),
                        TextInput::make('used_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        DateTimePicker::make('expires_at')
                            ->nullable(),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                    Tab::make('Targeting & Visibility')->icon('heroicon-o-eye')->schema([
                        TextInput::make('description')
                            ->label('Voucher Description / Title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_public')
                            ->label('Public Voucher (Show in Cart)')
                            ->default(true),
                        Toggle::make('is_new_user_only')
                            ->label('New User Only')
                            ->default(false),
                        Select::make('target_user_id')
                            ->relationship('targetUser', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Target Specific User (Optional)')
                            ->helperText('If selected, only this user can use the voucher.')
                            ->columnSpanFull(),
                    ])->columns(2),
                ])->columnSpanFull()
            ]);
    }
}

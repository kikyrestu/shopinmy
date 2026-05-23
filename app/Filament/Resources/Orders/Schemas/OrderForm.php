<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Order Tabs')->tabs([
                    Tab::make('Order Info')->icon('heroicon-o-information-circle')->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(fn ($record) => !is_null($record?->user_id)),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Select::make('voucher_id')
                            ->relationship('voucher', 'code')
                            ->searchable()
                            ->preload()
                            ->label('Voucher'),
                    ])->columns(3),

                    Tab::make('Cost & Items')->icon('heroicon-o-shopping-cart')->schema([
                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->prefix('RM'),
                        TextInput::make('shipping_cost')
                            ->numeric()
                            ->disabled()
                            ->prefix('RM'),
                        TextInput::make('tax_rate')
                            ->numeric()
                            ->suffix('%')
                            ->disabled(),
                        TextInput::make('tax_amount')
                            ->numeric()
                            ->prefix('RM')
                            ->disabled(),
                        
                        Repeater::make('items')
                            ->relationship()
                            ->disabled() // Items should not be modified here directly
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name'),
                                Select::make('variant_id')
                                    ->relationship('variant', 'name')
                                    ->label('Variant (if any)'),
                                TextInput::make('qty')
                                    ->numeric(),
                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('RM'),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                    ])->columns(4),

                    Tab::make('Shipping')->icon('heroicon-o-truck')->schema([
                        Select::make('address_id')
                            ->relationship('address', 'address')
                            ->disabled(),
                        TextInput::make('order_number')->disabled(),
                        TextInput::make('courier'),
                        TextInput::make('tracking_no')->label('Tracking No (MyParcel)'),
                    ])->columns(2),

                    Tab::make('Guest Info')->icon('heroicon-o-user')->schema([
                        TextInput::make('guest_email')->email(),
                        TextInput::make('guest_name'),
                        TextInput::make('guest_phone'),
                        TextInput::make('guest_address')->columnSpanFull(),
                        TextInput::make('guest_city'),
                        TextInput::make('guest_state'),
                        TextInput::make('guest_postcode'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null && is_null($record->user_id)),
                ])->columnSpanFull()
            ]);
    }
}

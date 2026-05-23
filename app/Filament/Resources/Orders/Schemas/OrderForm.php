<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Order Information')
                            ->columnSpan(2)
                            ->schema([
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
                            ]),

                        Section::make('Shipping')
                            ->columnSpan(1)
                            ->schema([
                                Select::make('address_id')
                                    ->relationship('address', 'address')
                                    ->disabled(),
                                TextInput::make('order_number')->disabled(),
                                TextInput::make('courier'),
                                TextInput::make('tracking_no')->label('Tracking No (MyParcel)'),
                            ]),

                        Section::make('Guest Info')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('guest_email')->email(),
                                TextInput::make('guest_name'),
                                TextInput::make('guest_phone'),
                                TextInput::make('guest_address'),
                                TextInput::make('guest_city'),
                                TextInput::make('guest_state'),
                                TextInput::make('guest_postcode'),
                            ])
                            ->visible(fn ($record) => $record !== null && is_null($record->user_id))
                            ->collapsed(),
                    ])
            ]);
    }
}

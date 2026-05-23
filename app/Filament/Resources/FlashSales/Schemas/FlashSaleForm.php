<?php

namespace App\Filament\Resources\FlashSales\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class FlashSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Flash Sale Tabs')->tabs([
                    Tab::make('Campaign Info')->icon('heroicon-o-megaphone')->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('starts_at')
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->required()
                            ->after('starts_at'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->columnSpanFull(),
                    ])->columns(2),

                    Tab::make('Products & Quota')->icon('heroicon-o-shopping-bag')->schema([
                        Repeater::make('flashSaleProducts')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(\App\Models\Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('sale_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('RM'),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->nullable()
                                    ->label('Kuota (kosong = unlimited)'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->label('Produk Flash Sale'),
                    ]),
                ])->columnSpanFull()
            ]);
    }
}

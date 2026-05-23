<?php

namespace App\Filament\Resources\Bundles\Schemas;

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

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Bundle Tabs')->tabs([
                    Tab::make('Bundle Info')->icon('heroicon-o-gift')->schema([
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
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('RM')
                            ->label('Harga Bundle'),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                    Tab::make('Bundle Items')->icon('heroicon-o-cube')->schema([
                        Repeater::make('bundleProducts')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(\App\Models\Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->label('Produk dalam Bundle'),
                    ]),
                ])->columnSpanFull()
            ]);
    }
}

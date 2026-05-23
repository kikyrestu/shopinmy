<?php

namespace App\Filament\Resources\Bundles\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->rows(3),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('RM')
                    ->label('Harga Bundle'),
                Toggle::make('is_active')
                    ->default(true),

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
                    ->label('Produk dalam Bundle'),
            ]);
    }
}

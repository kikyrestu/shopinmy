<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product')->tabs([
                    Tabs\Tab::make('General Information')
                        ->schema([
                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable(),
                            Select::make('brand_id')
                                ->relationship('brand', 'name')
                                ->searchable(),
                            TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true),
                            RichEditor::make('description')
                                ->columnSpanFull(),
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('RM'),
                            TextInput::make('weight')
                                ->numeric()
                                ->default(0.500)
                                ->suffix('kg')
                                ->helperText('Berat produk dalam kilogram'),
                            TextInput::make('stock')
                                ->numeric()
                                ->nullable()
                                ->helperText('Leave empty if using variants stock.'),
                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                            Toggle::make('is_free_shipping')
                                ->label('Penghantaran Percuma / Free Shipping')
                                ->default(false),
                            TextInput::make('warranty_period')
                                ->label('Periode Garansi')
                                ->placeholder('Contoh: 1 Year / 6 Months')
                                ->nullable()
                                ->columnSpanFull(),
                        ])->columns(2),
                    
                    Tabs\Tab::make('Images')
                        ->schema([
                            FileUpload::make('images')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->directory('products')
                                ->disk('public')
                                ->imageEditor()
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeTargetWidth('1080')
                                ->imageResizeTargetHeight('1080')
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('Variants')
                        ->schema([
                            Repeater::make('variants')
                                ->relationship()
                                ->schema([
                                    TextInput::make('name')->required()->placeholder('Size / Color'),
                                    TextInput::make('value')->required()->placeholder('XL / Red'),
                                    TextInput::make('price_modifier')->numeric()->default(0)->prefix('RM'),
                                    TextInput::make('stock')->numeric()->default(0),
                                    TextInput::make('sku'),
                                ])->columns(5)->columnSpanFull()
                        ]),

                    Tabs\Tab::make('SEO')
                        ->schema([
                            TextInput::make('meta_title'),
                            Textarea::make('meta_description')
                                ->columnSpanFull(),
                            TextInput::make('meta_keywords'),
                        ])
                ])->columnSpanFull()
            ]);
    }
}

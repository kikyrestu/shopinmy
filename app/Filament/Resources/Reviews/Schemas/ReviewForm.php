<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled()
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->disabled()
                    ->nullable(),
                TextInput::make('rating')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->required(),
                Textarea::make('comment')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}

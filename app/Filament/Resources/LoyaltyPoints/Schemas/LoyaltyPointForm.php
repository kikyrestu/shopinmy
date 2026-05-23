<?php

namespace App\Filament\Resources\LoyaltyPoints\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class LoyaltyPointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Loyalty Point')->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('points')
                    ->numeric()
                    ->required(),
                Select::make('type')
                    ->options([
                        'earned' => 'Earned',
                        'redeemed' => 'Redeemed',
                        'expired' => 'Expired',
                        'adjustment' => 'Adjustment',
                    ])
                    ->required(),
                TextInput::make('description'),
            ])->columns(2),
        ]);
    }
}

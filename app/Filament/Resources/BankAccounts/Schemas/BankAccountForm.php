<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_number')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo')
                    ->image()
                    ->directory('bank-logos')
                    ->label('Logo Bank'),
                TextInput::make('sort')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}

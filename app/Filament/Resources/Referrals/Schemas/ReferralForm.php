<?php

namespace App\Filament\Resources\Referrals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ReferralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Referral Details')->schema([
                Select::make('referrer_id')
                    ->relationship('referrer', 'name')
                    ->label('Referrer')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('referee_id')
                    ->relationship('referee', 'name')
                    ->label('Referee (New Customer)')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('code')
                    ->label('Referral Code')
                    ->required(),
                Toggle::make('reward_given')
                    ->label('Reward Given'),
            ])->columns(2),
        ]);
    }
}

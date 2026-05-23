<?php

namespace App\Filament\Resources\Referrals\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referrer.name')
                    ->label('Referrer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referee.name')
                    ->label('New Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->badge()
                    ->copyable()
                    ->searchable(),
                IconColumn::make('reward_given')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\LoyaltyPoints\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyPointsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'earned' => 'success',
                        'redeemed' => 'warning',
                        'expired' => 'gray',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc');
    }
}

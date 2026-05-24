<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        'free_shipping' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->type === 'percentage' ? "{$record->value}%" : "RM {$record->value}"),
                TextColumn::make('min_order')
                    ->formatStateUsing(fn ($state) => 'RM ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Used / Limit')
                    ->formatStateUsing(fn ($record) => "{$record->used_count} / " . ($record->usage_limit ?? '∞')),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                IconColumn::make('is_new_user_only')
                    ->label('New User Only')
                    ->boolean(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

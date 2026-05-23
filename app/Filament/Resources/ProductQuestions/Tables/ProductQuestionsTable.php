<?php

namespace App\Filament\Resources\ProductQuestions\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ProductQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->limit(25)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Asked by')
                    ->searchable(),
                TextColumn::make('question')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('answer')
                    ->limit(40)
                    ->placeholder('Belum dijawab')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                IconColumn::make('is_published')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                Action::make('publish')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_published && $record->answer)
                    ->action(function ($record) {
                        $record->update(['is_published' => true]);
                        Notification::make()->title('Q&A Published')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

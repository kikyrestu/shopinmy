<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NewsletterExport;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('unsubscribed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Active')
                    ->color(fn ($state) => $state ? 'danger' : null),
            ])
            ->filters([])
            ->headerActions([
                Action::make('export')
                    ->label('Export Subscribers')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        if (class_exists(NewsletterExport::class)) {
                            return Excel::download(new NewsletterExport, 'subscribers_' . now()->format('Ymd') . '.xlsx');
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
    }
}

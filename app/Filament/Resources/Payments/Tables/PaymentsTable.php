<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->label('Order ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('method')
                    ->searchable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => 'RM ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'primary',
                    }),
                ImageColumn::make('proof_image')
                    ->disk('public')
                    ->label('Bukti Transfer'),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('export_payments')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn () => Excel::download(new PaymentsExport, 'payments_' . now()->format('Ymd') . '.xlsx')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('verify')
                    ->label(fn ($record) => $record->method === 'cod' ? 'Mark COD Received' : 'Approve Transfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->method, ['manual_transfer', 'cod']) && $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paid',
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        $record->order->update([
                            'status' => 'paid',
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject Transfer')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->helperText('Beritahu customer mengapa bukti transfer ditolak (misal: Foto blur/Nominal tidak sesuai).')
                    ])
                    ->visible(fn ($record) => $record->method === 'manual_transfer' && $record->status === 'pending')
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => 'failed',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

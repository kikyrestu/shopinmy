<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\MyParcelService;
use App\Services\InvoiceService;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('order_number')
                    ->label('Order No')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'RM ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('tracking_no')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('export_orders')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn () => Excel::download(new OrdersExport, 'orders_' . now()->format('Ymd') . '.xlsx')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('verify_payment')
                    ->label('Verifikasi Pembayaran')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment && $record->payment->method === 'manual_transfer' && $record->payment->status === 'pending')
                    ->modalHeading('Verifikasi Bukti Transfer')
                    ->modalWidth('2xl')
                    ->modalContent(fn ($record) => view('filament.components.payment-proof', ['payment' => $record->payment]))
                    ->modalSubmitActionLabel('Terima (Approve)')
                    ->action(function ($record, array $data) {
                        $record->payment->update([
                            'status' => 'paid',
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        $record->update(['status' => 'paid']);
                        Notification::make()->title('Pembayaran Disetujui')->success()->send();
                    })
                    ->extraModalFooterActions(fn ($action, $record) => [
                        Action::make('reject_payment')
                            ->label('Tolak (Reject)')
                            ->color('danger')
                            ->form([
                                \Filament\Forms\Components\Textarea::make('rejection_reason')
                                    ->label('Alasan Penolakan')
                                    ->placeholder('Misal: Foto buram atau nominal tidak sesuai')
                                    ->required()
                            ])
                            ->action(function (array $data) use ($record, $action) {
                                $record->payment->update([
                                    'status' => 'failed',
                                    'rejection_reason' => $data['rejection_reason'],
                                    'verified_at' => now(),
                                    'verified_by' => auth()->id(),
                                ]);
                                Notification::make()->title('Pembayaran Ditolak')->danger()->send();
                                $action->cancel();
                            }),
                    ]),
                Action::make('download_invoice')
                    ->label('Invoice PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['paid', 'processing', 'shipped', 'completed']))
                    ->action(fn ($record) => InvoiceService::download($record)),
                Action::make('generate_awb')
                    ->label('Generate AWB')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['paid', 'processing']) && empty($record->tracking_no))
                    ->action(function ($record) {
                        try {
                            $myParcel = new \App\Services\MyParcelService();
                            $trackingNo = $myParcel->generateAwbForOrder($record);

                            Notification::make()
                                ->title($trackingNo ? 'AWB Generated: ' . $trackingNo : 'AWB Generated (Resi Pending)')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error generating AWB')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('sync_awb')
                    ->label('Sync Resi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['paid', 'processing']) && empty($record->tracking_no))
                    ->action(function ($record) {
                        try {
                            $myParcel = new \App\Services\MyParcelService();
                            $trackingNo = $myParcel->syncTrackingNumber($record);

                            if ($trackingNo) {
                                Notification::make()
                                    ->title('Resi Synced: ' . $trackingNo)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Resi Not Found')
                                    ->body('The tracking number is not available yet or not found in recent history.')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error syncing resi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('trace_shipment')
                    ->label('Track')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->tracking_no) && !str_starts_with($record->tracking_no, 'ORD-'))
                    ->action(function ($record) {
                        try {
                            $myParcel = new MyParcelService();
                            $trace = $myParcel->trace($record->tracking_no);

                            Notification::make()
                                ->title('Status: ' . ucfirst($trace['status'] ?? 'unknown'))
                                ->body('Updated: ' . ($trace['updated_at'] ?? '-'))
                                ->info()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Tracking unavailable')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Pesanan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->formatStateUsing(fn ($state) => '#ORD-' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->formatStateUsing(fn ($state, $record) => $state ?? $record->guest_name ?? 'Guest')
                    ->description(fn ($record) => $record->user?->email ?? $record->guest_email ?? '-')
                    ->searchable(['user.name', 'guest_name', 'user.email', 'guest_email']),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Bayar',
                        'paid' => 'Sudah Bayar',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'RM ' . number_format($state, 2))
                    ->sortable(),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}

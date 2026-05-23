<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Exports\ProductsExport;
use App\Exports\StockExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->sortable(),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => 'RM ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state !== null && $state <= 5 ? 'danger' : 'success'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('export_products')
                    ->label('Export Produk')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn () => Excel::download(new ProductsExport, 'products_' . now()->format('Ymd') . '.xlsx')),
                Action::make('export_stock')
                    ->label('Export Stok')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(fn () => Excel::download(new StockExport, 'stock_' . now()->format('Ymd') . '.xlsx')),
                Action::make('import_products')
                    ->label('Import Produk')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel (.xlsx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->directory('imports'),
                    ])
                    ->action(function (array $data) {
                        try {
                            Excel::import(new ProductsImport, storage_path('app/public/' . $data['file']));
                            Notification::make()->title('Import produk berhasil!')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Import gagal')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
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

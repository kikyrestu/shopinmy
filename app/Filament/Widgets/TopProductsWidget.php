<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*', 
                        DB::raw('(SELECT SUM(qty) FROM order_items WHERE product_id = products.id) as total_sold'),
                        DB::raw('(SELECT SUM(qty * price) FROM order_items WHERE product_id = products.id) as total_revenue')
                    )
                    ->having('total_sold', '>', 0)
                    ->orderByDesc('total_sold')
                    ->limit(5)
            )
            ->heading('Produk Terlaris')
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder-product.png')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->description(fn (Product $record): string => $record->category->name ?? 'Uncategorized')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Terjual')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Pendapatan')
                    ->money('MYR')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}

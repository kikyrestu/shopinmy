<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Widget;

class TopProductsWidget extends Widget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected string $view = 'filament.widgets.top-products';

    public function getTopProducts(): \Illuminate\Support\Collection
    {
        return DB::table('order_items')
            ->select('products.id', 'products.name', 'products.price', 'categories.name as category_name',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.qty * order_items.price) as total_revenue')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('products.id', 'products.name', 'products.price', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }
}

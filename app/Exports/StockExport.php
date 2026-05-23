<?php

namespace App\Exports;

use App\Models\ProductVariant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ProductVariant::with('product')->get();
    }

    public function headings(): array
    {
        return ['Product', 'Variant', 'SKU', 'Stock', 'Price Modifier (RM)'];
    }

    public function map($variant): array
    {
        return [
            $variant->product?->name ?? '-',
            $variant->name . ': ' . $variant->value,
            $variant->sku ?? '-',
            $variant->stock,
            number_format($variant->price_modifier, 2),
        ];
    }
}

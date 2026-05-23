<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with(['category', 'brand', 'variants'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Slug', 'Category', 'Brand', 'Price (RM)', 'Status', 'Variants'];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->slug,
            $product->category?->name ?? '-',
            $product->brand?->name ?? '-',
            number_format($product->price, 2),
            $product->is_active ? 'Active' : 'Inactive',
            $product->variants->count(),
        ];
    }
}

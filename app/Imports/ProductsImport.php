<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        $category = Category::firstOrCreate(
            ['name' => $row['category']],
            ['slug' => Str::slug($row['category'])]
        );

        $brand = !empty($row['brand'])
            ? Brand::firstOrCreate(
                ['name' => $row['brand']],
                ['slug' => Str::slug($row['brand'])]
            )
            : null;

        return new Product([
            'name'             => $row['name'],
            'slug'             => Str::slug($row['name']) . '-' . uniqid(),
            'category_id'      => $category->id,
            'brand_id'         => $brand?->id,
            'description'      => $row['description'] ?? null,
            'price'            => $row['price'],
            'is_active'        => $row['is_active'] ?? 1,
            'meta_title'       => $row['meta_title'] ?? null,
            'meta_description' => $row['meta_description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string',
            'category' => 'required|string',
            'price'    => 'required|numeric|min:0',
        ];
    }
}

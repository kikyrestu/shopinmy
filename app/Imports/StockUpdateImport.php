<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class StockUpdateImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        $variant = ProductVariant::where('sku', $row['sku'])->first();

        if ($variant) {
            $variant->update(['stock' => $row['stock']]);
        }

        return null; // We're updating, not creating
    }

    public function rules(): array
    {
        return [
            'sku'   => 'required|string',
            'stock' => 'required|integer|min:0',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleProduct extends Model
{
    protected $guarded = [];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

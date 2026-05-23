<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
            ->withPivot('sale_price', 'qty')
            ->withTimestamps();
    }

    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function isRunning(): bool
    {
        return $this->is_active && now()->between($this->starts_at, $this->ends_at);
    }
}

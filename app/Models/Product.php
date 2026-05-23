<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function questions()
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function restockAlerts()
    {
        return $this->hasMany(RestockAlert::class);
    }

    public function flashSales()
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')
            ->withPivot('sale_price', 'qty')
            ->withTimestamps();
    }

    public function getActiveFlashSaleAttribute()
    {
        if ($this->relationLoaded('flashSales')) {
            return $this->flashSales
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->first();
        }
        
        return $this->flashSales()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    public function getIsOnFlashSaleAttribute(): bool
    {
        return $this->active_flash_sale !== null;
    }

    public function getActivePriceAttribute()
    {
        if ($this->is_on_flash_sale) {
            return $this->active_flash_sale->pivot->sale_price;
        }

        return $this->price;
    }

    public function getFirstImageUrlAttribute()
    {
        if ($this->primaryImage) {
            return \Illuminate\Support\Facades\Storage::url($this->primaryImage->path);
        }
        
        if (!empty($this->images) && isset($this->images[0])) {
            return \Illuminate\Support\Facades\Storage::url($this->images[0]);
        }
        
        return asset('images/placeholder-product.png');
    }
}

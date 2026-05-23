<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $guarded = [];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function getEffectivePriceAttribute()
    {
        if ($this->bundle_id && $this->bundle) {
            $bundle = $this->bundle;
            $bundle->loadMissing('products');
            
            $totalOriginalPrice = 0;
            $thisPivotQty = 1;
            foreach ($bundle->products as $bp) {
                $totalOriginalPrice += ($bp->price * $bp->pivot->qty);
                if ($bp->id == $this->product_id) {
                    $thisPivotQty = $bp->pivot->qty;
                }
            }

            if ($totalOriginalPrice > 0) {
                $proportion = ($this->product->price * $thisPivotQty) / $totalOriginalPrice;
                $bundlePriceAllocatedToThisProduct = $bundle->price * $proportion;
                return $bundlePriceAllocatedToThisProduct / $thisPivotQty;
            }
        }

        $price = $this->product->active_price;
        if ($this->variant && $this->variant->price_modifier) {
            $price += $this->variant->price_modifier;
        }

        return $price;
    }
}

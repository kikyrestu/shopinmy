<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_products')
            ->withPivot('qty')
            ->withTimestamps();
    }

    public function bundleProducts()
    {
        return $this->hasMany(BundleProduct::class);
    }
}

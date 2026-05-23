<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockAlert extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

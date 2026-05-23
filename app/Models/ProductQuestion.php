<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

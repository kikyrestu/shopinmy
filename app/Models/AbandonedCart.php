<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reminded_at' => 'datetime',
        'converted' => 'boolean',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

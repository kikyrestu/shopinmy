<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleProduct extends Model
{
    protected $guarded = [];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

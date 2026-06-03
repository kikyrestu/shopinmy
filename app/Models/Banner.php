<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return \Storage::disk('public')->url($this->image);
    }

    public function getMobileImageUrlAttribute()
    {
        $path = $this->mobile_image ?? $this->image;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return \Storage::disk('public')->url($path);
    }
}

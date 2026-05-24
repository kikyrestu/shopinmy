<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'usage_limit',
        'user_usage_limit',
        'used_count',
        'expires_at',
        'is_active',
        'is_public',
        'is_new_user_only',
        'target_user_id',
        'description',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getUserUsageCountAttribute()
    {
        if (!auth()->check()) return 0;
        return \App\Models\Order::where('user_id', auth()->id())
            ->where('voucher_id', $this->id)
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function getEligibilityError($subtotal)
    {
        if ($this->min_order > 0 && $subtotal < $this->min_order) {
            return 'Minimal belanja RM ' . number_format($this->min_order, 2) . ' belum tercapai.';
        }

        if ($this->user_usage_limit !== null) {
            if ($this->user_usage_count >= $this->user_usage_limit) {
                return 'Kuota pemakaian voucher untuk akun ini sudah habis (Maks ' . $this->user_usage_limit . 'x).';
            }
        }
        
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'Kuota global voucher ini sudah habis.';
        }

        return null;
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}

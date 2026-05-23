<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return cache()->rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) return $default;

            if ($setting->is_encrypted && $setting->value !== null) {
                return Crypt::decryptString($setting->value);
            }

            return $setting->value;
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted ? Crypt::encryptString($value) : $value,
                'group' => $group,
                'is_encrypted' => $encrypted,
            ]
        );
        cache()->forget("setting:{$key}");
    }

    /**
     * Check if a setting is enabled
     */
    public static function isEnabled(string $key): bool
    {
        return filter_var(static::get($key), FILTER_VALIDATE_BOOLEAN);
    }
}

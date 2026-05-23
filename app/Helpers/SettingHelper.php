<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, $default = null): mixed
    {
        return cache()->rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) return $default;

            return $setting->is_encrypted
                ? \Illuminate\Support\Facades\Crypt::decryptString($setting->value)
                : $setting->value;
        });
    }
}

if (!function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false): bool
    {
        return filter_var(setting($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
}

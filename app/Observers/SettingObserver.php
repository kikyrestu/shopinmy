<?php

namespace App\Observers;

use App\Models\Setting;

class SettingObserver
{
    public function saved(Setting $setting): void
    {
        cache()->forget("setting:{$setting->key}");
    }

    public function deleted(Setting $setting): void
    {
        cache()->forget("setting:{$setting->key}");
    }
}

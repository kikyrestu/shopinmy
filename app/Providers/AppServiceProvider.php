<?php

namespace App\Providers;

use App\Models\Setting;
use App\Observers\SettingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production or if APP_URL uses https
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Setting::observe(SettingObserver::class);
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // Inject Google Socialite config from settings
        try {
            $googleClientId = Setting::get('google_client_id');
            $googleClientSecret = Setting::get('google_client_secret');
            if ($googleClientId && $googleClientSecret) {
                config([
                    'services.google' => [
                        'client_id' => $googleClientId,
                        'client_secret' => $googleClientSecret,
                        'redirect' => url('/auth/google/callback'),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Abaikan error kalau table belum di-migrate (deploy pertama kali)
        }

        \Illuminate\Support\Facades\View::composer('layouts.storefront', function ($view) {
            $view->with('categories', \App\Models\Category::with('children')->whereNull('parent_id')->get());
            $view->with('footerPages', \App\Models\Page::select('title', 'slug')->get());
        });
    }
}

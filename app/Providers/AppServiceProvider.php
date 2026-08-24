<?php

namespace App\Providers;

use App\Services\SystemSettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('Support/helpers.php');

        if (is_file($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(SystemSettingsService::class)->apply();
    }
}

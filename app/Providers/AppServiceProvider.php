<?php

namespace App\Providers;

use App\Models\UserNotification;
use App\Services\SystemSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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

        View::composer('layouts.panel', function ($view): void {
            $user = auth()->user();

            if (! $user || ! Schema::hasTable('notificaciones_usuarios')) {
                $view->with(['headerNotifications' => collect(), 'unreadNotificationCount' => 0]);
                return;
            }

            $notifications = UserNotification::query()
                ->where('usuario_id', $user->id)
                ->latest()
                ->limit(6)
                ->get();

            $view->with([
                'headerNotifications' => $notifications,
                'unreadNotificationCount' => $notifications->whereNull('leida_en')->count(),
            ]);
        });
    }
}

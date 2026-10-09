<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureMenuAccess;
use App\Http\Middleware\EnsureRoleAccess;
use App\Http\Middleware\EnsureStrongPassword;
use App\Services\TelegramErrorNotifier;
use App\Support\AppUrl;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'menu.access' => EnsureMenuAccess::class,
            'role.access' => EnsureRoleAccess::class,
            'user.active' => EnsureActiveUser::class,
            'password.strong' => EnsureStrongPassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $exception) {
            app(TelegramErrorNotifier::class)->send($exception, request());
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419) {
                return redirect(AppUrl::route('login'))
                    ->with('status', 'La sesion expiro. Ingresa nuevamente para continuar.');
            }

            return null;
        });
    })->create();

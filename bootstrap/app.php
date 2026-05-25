<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'menu.access' => \App\Http\Middleware\EnsureMenuAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $exception, \Illuminate\Http\Request $request) {
            if ($exception->getStatusCode() === 419) {
                return redirect()
                    ->route('login')
                    ->with('status', 'La sesion expiro. Ingresa nuevamente para continuar.');
            }

            return null;
        });
    })->create();

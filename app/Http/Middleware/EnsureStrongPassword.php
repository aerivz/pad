<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Support\AppUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStrongPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! (bool) $request->session()->get('password_change_required', false)) {
            return $next($request);
        }

        $allowedActions = [
            AuthController::class.'@destroy',
            ProfileController::class.'@show',
            ProfileController::class.'@update',
            ProfileController::class.'@updateTheme',
        ];

        if (in_array($request->route()?->getActionName(), $allowedActions, true)) {
            return $next($request);
        }

        return redirect(AppUrl::route('profile.show'))
            ->with('error', 'Debes cambiar tu contrasena antes de continuar usando el sistema.');
    }
}

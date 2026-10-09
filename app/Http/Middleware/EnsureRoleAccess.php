<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $roleName = $user?->role()->where('activo', true)->value('nombre');

        abort_unless($user && in_array($roleName, $roles, true), 403);

        return $next($request);
    }
}

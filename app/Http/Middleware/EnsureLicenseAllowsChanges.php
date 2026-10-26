<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseAllowsChanges
{
    public function __construct(private readonly LicenseService $licenses)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || $this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        $status = $this->licenses->status();

        if ($status['allows_changes']) {
            return $next($request);
        }

        $message = 'La aplicacion esta en modo solo lectura. '.$status['message'].' Contacta al proveedor para renovar el servicio.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'license_state' => $status['state'],
            ], 423);
        }

        return back()->with('error', $message);
    }

    private function isAlwaysAllowed(Request $request): bool
    {
        return $request->routeIs('logout', 'license.update')
            || $request->is('logout', 'pad/logout', 'licencia', 'pad/licencia');
    }
}

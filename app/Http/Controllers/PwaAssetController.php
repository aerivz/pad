<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaAssetController extends Controller
{
    public function manifest(): Response
    {
        return $this->asset('manifest.webmanifest', 'application/manifest+json; charset=utf-8', [
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function serviceWorker(): Response
    {
        return $this->asset('service-worker.js', 'application/javascript; charset=utf-8', [
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function offline(): Response
    {
        return $this->asset('offline.html', 'text/html; charset=utf-8');
    }

    public function icon(string $name): Response
    {
        abort_unless(in_array($name, ['icon-192.png', 'icon-512.png'], true), 404);

        return $this->asset('images/pwa/'.$name, 'image/png');
    }

    private function asset(string $path, string $contentType, array $headers = []): Response
    {
        $fullPath = public_path($path);
        abort_unless(is_file($fullPath), 404);

        return response(file_get_contents($fullPath), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=3600',
            ...$headers,
        ]);
    }
}

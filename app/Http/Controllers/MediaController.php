<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse|Response
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        if (! $this->isAllowedPath($normalized)) {
            abort(404);
        }

        $fullPath = public_path($normalized);

        if (! is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function isAllowedPath(string $path): bool
    {
        return Str::startsWith($path, [
            'uploads/',
            'images/',
        ]);
    }
}

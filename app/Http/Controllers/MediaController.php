<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse|Response
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        if ($normalized === '' || in_array('..', explode('/', $normalized), true)) {
            abort(404);
        }

        $fullPath = realpath(public_path($normalized));

        if ($fullPath === false || ! is_file($fullPath) || ! $this->isAllowedPath($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function isAllowedPath(string $path): bool
    {
        foreach ([public_path('uploads'), public_path('images')] as $directory) {
            $root = realpath($directory);

            if ($root !== false && str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }
}

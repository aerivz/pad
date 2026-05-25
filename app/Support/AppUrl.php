<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AppUrl
{
    public static function menu(?string $target = null): string
    {
        if ($target === null || trim($target) === '') {
            return self::applicationRoot();
        }

        $target = trim($target);

        if (self::isExternal($target) || $target === '#') {
            return $target;
        }

        $normalized = self::normalizeInternalPath($target);

        if ($normalized === '') {
            return self::applicationRoot();
        }

        return url($normalized);
    }

    public static function media(?string $path = null, string $default = 'images/defaults/image.svg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            $path = $default;
        }

        if (self::isExternal($path)) {
            return $path;
        }

        $normalized = self::normalizeMediaPath($path);

        return self::menu('media/'.$normalized);
    }

    public static function applicationRoot(): string
    {
        if (Route::has('dashboard')) {
            return route('dashboard');
        }

        return url('/');
    }

    public static function normalizeInternalPath(string $path): string
    {
        $path = self::stripHost($path);
        $path = preg_replace('#^/?public/#i', '', $path) ?? $path;

        $appPrefix = trim(parse_url(Route::has('dashboard') ? route('dashboard', [], false) : '/', PHP_URL_PATH) ?? '', '/');
        $trimmed = trim($path, '/');

        if ($appPrefix !== '') {
            if ($trimmed === $appPrefix) {
                return $appPrefix;
            }

            if (Str::startsWith($trimmed, $appPrefix.'/')) {
                return $trimmed;
            }
        }

        if ($trimmed === '') {
            return $appPrefix;
        }

        return trim($appPrefix.'/'.$trimmed, '/');
    }

    public static function normalizeMediaPath(string $path): string
    {
        $path = self::stripHost($path);
        $path = preg_replace('#^/?public/#i', '', $path) ?? $path;
        $path = trim($path, '/');

        if ($path === '') {
            return 'images/defaults/image.svg';
        }

        return $path;
    }

    public static function isExternal(string $path): bool
    {
        return Str::startsWith($path, ['http://', 'https://', '//', 'mailto:', 'tel:']);
    }

    private static function stripHost(string $path): string
    {
        $parts = parse_url($path);

        if (($parts['scheme'] ?? null) !== null || ($parts['host'] ?? null) !== null) {
            $path = $parts['path'] ?? '';
        }

        return $path;
    }
}

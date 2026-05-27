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

        return '/'.ltrim($normalized, '/');
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
        $prefix = self::runtimePrefix();

        return $prefix === '' ? '/' : '/'.$prefix;
    }

    public static function route(string $name, array $parameters = []): string
    {
        if (! Route::has($name)) {
            return self::applicationRoot();
        }

        $path = parse_url(route($name, $parameters, false), PHP_URL_PATH) ?? '/';

        return self::menu($path);
    }

    public static function normalizeInternalPath(string $path): string
    {
        $path = self::stripHost($path);
        $path = preg_replace('#^/?public/#i', '', $path) ?? $path;

        $canonicalPrefix = trim(parse_url(Route::has('dashboard') ? route('dashboard', [], false) : '/', PHP_URL_PATH) ?? '', '/');
        $runtimePrefix = self::runtimePrefix();
        $trimmed = trim($path, '/');

        if ($canonicalPrefix !== '' && $runtimePrefix !== $canonicalPrefix) {
            if ($trimmed === $canonicalPrefix) {
                $trimmed = '';
            } elseif (Str::startsWith($trimmed, $canonicalPrefix.'/')) {
                $trimmed = substr($trimmed, strlen($canonicalPrefix) + 1);
            }
        }

        if ($runtimePrefix !== '') {
            if ($trimmed === $runtimePrefix) {
                return $runtimePrefix;
            }

            if (Str::startsWith($trimmed, $runtimePrefix.'/')) {
                return $trimmed;
            }
        }

        if ($trimmed === '') {
            return $runtimePrefix;
        }

        return trim($runtimePrefix.'/'.$trimmed, '/');
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

    private static function runtimePrefix(): string
    {
        $segment = trim((string) request()->segment(1), '/');

        if ($segment === 'pad') {
            return 'pad';
        }

        return '';
    }
}

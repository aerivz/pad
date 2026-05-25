<?php

use App\Support\AppUrl;

if (! function_exists('app_nav_url')) {
    function app_nav_url(?string $target = null): string
    {
        return AppUrl::menu($target);
    }
}

if (! function_exists('app_media_url')) {
    function app_media_url(?string $path = null, string $default = 'images/defaults/image.svg'): string
    {
        return AppUrl::media($path, $default);
    }
}

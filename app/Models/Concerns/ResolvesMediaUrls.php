<?php

namespace App\Models\Concerns;

trait ResolvesMediaUrls
{
    public function resolveMediaUrl(?string $path = null, string $default = 'images/defaults/image.svg'): string
    {
        return app_media_url($path, $default);
    }
}

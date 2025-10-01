<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

class Asset
{
    public function url(string $path): string
    {
        $manifestPath = public_path('vendor/stickleapp/core/manifest.json');

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (array_key_exists($path, $manifest)) {
                return asset('vendor/stickleapp/core/'.ltrim($manifest[$path]['file'], '/'));
            }
        }

        return asset('vendor/stickleapp/core/'.ltrim($path, '/'));
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

class Asset
{
    public function url(string $path): string
    {
        $manifestPath = public_path('vendor/stickleapp/core/manifest.json');

        if (file_exists($manifestPath)) {
            $contents = file_get_contents($manifestPath);
            if ($contents !== false) {
                $manifest = json_decode($contents, true);
                if (is_array($manifest) && array_key_exists($path, $manifest)) {
                    return asset('vendor/stickleapp/core/'.ltrim((string) $manifest[$path]['file'], '/'));
                }
            }
        }

        return asset('vendor/stickleapp/core/'.ltrim($path, '/'));
    }
}

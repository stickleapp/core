<?php

declare(strict_types=1);

if (! function_exists('stickle_asset')) {
    /**
     * Generate a URL for a Stickle package asset.
     */
    function stickle_asset(string $path): string
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

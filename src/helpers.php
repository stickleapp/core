<?php

if (! function_exists('stickle_asset')) {
    function stickle_asset(string $path): string
    {
        $manifestPath = public_path('vendor/stickleapp/core/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (array_key_exists($path, $manifest)) {
                return asset('vendor/stickleapp/core/'.ltrim($manifest[$path]['file'], '/'));

            }
            $path = $manifest[$path] ?? $path;
        }

        return asset('vendor/stickleapp/core/'.ltrim($path, '/'));
    }
}

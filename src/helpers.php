<?php

declare(strict_types=1);
use Illuminate\Foundation\ViteManifestNotFoundException;

if (! function_exists('stickle_asset')) {
    /**
     * Generate a URL for a Stickle package asset.
     *
     * @deprecated Use `{{ app('stickle.vite') }}` in a layout instead, which emits
     *             the preload, stylesheet and module tags in one call. This shim
     *             remains only for host apps running a published copy of
     *             default-layout.blade.php from before that change.
     *
     * @throws ViteManifestNotFoundException when the package
     *                                       assets have not been published. It previously returned an unresolvable
     *                                       URL here, turning a missing publish step into a silent 404.
     */
    function stickle_asset(string $path): string
    {
        return resolve('stickle.vite')->asset($path);
    }
}

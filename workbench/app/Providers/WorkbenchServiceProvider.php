<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SplFileInfo;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /**
         * I don't love this but it's the only way to get the class names to be discovered
         */
        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file, $basePath) {

            $basePath = env('PACKAGE_PATH');

            $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()));

            $class = Str::replaceFirst('app', 'App', $class);

            $class = Str::replaceLast('.php', '', $class);

            $parts = array_map('ucfirst', explode(DIRECTORY_SEPARATOR, $class));

            return implode('\\', $parts);
        });
    }
}

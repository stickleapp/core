<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SplFileInfo;
use Workbench\App\Commands\SendTestRequests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Workbench\App\Middleware\AuthInline;

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

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    SendTestRequests::class,
                ],
            );
        }

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

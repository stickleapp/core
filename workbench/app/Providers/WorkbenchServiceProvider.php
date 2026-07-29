<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Override;
use SplFileInfo;
use Workbench\App\Commands\SendTestRequests;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        Broadcast::routes();

        // The workbench stands in as the host application, which is what has to
        // decide who may open Stickle. Everything is permitted here because the
        // dev server has no notion of an administrator.
        Gate::define('viewStickle', fn ($user = null): bool => true);

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
        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file, $basePath): string {

            // The package root, derived from this file's location rather than an
            // env var, so testbench.yaml.dist stays portable across machines.
            // workbench/app/Providers -> workbench/app -> workbench -> package root
            $basePath = dirname(__DIR__, 3);

            $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()));

            $class = Str::replaceFirst('app', 'App', $class);

            $class = Str::replaceLast('.php', '', $class);

            $parts = array_map(ucfirst(...), explode(DIRECTORY_SEPARATOR, $class));

            return implode('\\', $parts);
        });
    }
}

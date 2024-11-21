<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Providers;

use Dclaysmith\LaravelCascade\Console\Commands\StartCommand;
// use Dclaysmith\LaravelCascade\Console\Commands\InstallCommand;
use Dclaysmith\LaravelCascade\Middleware\InjectJavascriptLibrary;
use Dclaysmith\LaravelCascade\Middleware\RequestLogger;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

final class LaravelCascadeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(Kernel $kernel): void
    {
        $kernel = $this->app->make(Kernel::class);

        if (config('cascade.tracking.server.loadMiddleware') === true) {
            $kernel->pushMiddleware(RequestLogger::class);
        }

        if (config('cascade.tracking.client.loadMiddleware') === true) {
            $kernel->pushMiddleware(InjectJavascriptLibrary::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    // ExportSegments::class,
                    // LogSegmentStatistics::class,
                    // LogEntityStatistics::class,
                    // RollupEvents::class,
                    // RollupPageViews::class,
                    // RollupSessions::class,
                    // InstallCommand::class,
                    StartCommand::class,
                ],
            );
        }
    }
}

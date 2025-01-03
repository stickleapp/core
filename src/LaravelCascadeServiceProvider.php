<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade;

use Dclaysmith\LaravelCascade\Commands\CreatePartitions;
use Dclaysmith\LaravelCascade\Commands\DropPartitions;
use Dclaysmith\LaravelCascade\Commands\ExportSegments;
use Dclaysmith\LaravelCascade\Commands\ProcessSegmentEvents;
use Dclaysmith\LaravelCascade\Commands\RecordObjectAttributes;
use Dclaysmith\LaravelCascade\Commands\RecordSegmentStatistics;
use Dclaysmith\LaravelCascade\Commands\StartCommand;
// use Dclaysmith\LaravelCascade\Console\Commands\InstallCommand;
use Dclaysmith\LaravelCascade\Components\BlankLayout;
use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Middleware\InjectJavascriptTrackingCode;
use Dclaysmith\LaravelCascade\Middleware\RequestLogger;
use Dclaysmith\LaravelCascade\Models\ObjectAttribute;
use Dclaysmith\LaravelCascade\Observers\ObjectAttributeObserver;
use Dclaysmith\LaravelCascade\Repositories\PostgresAnalyticsRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class LaravelCascadeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);

        /**
         * Bind the Analytics Repository
         */
        $this->app->bind(AnalyticsRepository::class, PostgresAnalyticsRepository::class);
    }

    public function boot(Kernel $kernel): void
    {
        $kernel = $this->app->make(Kernel::class);

        ObjectAttribute::observe(ObjectAttributeObserver::class);

        if (config('cascade.tracking.server.loadMiddleware') === true) {
            $kernel->pushMiddleware(RequestLogger::class);
        }

        if (config('cascade.tracking.client.loadMiddleware') === true) {
            $kernel->pushMiddleware(InjectJavascriptTrackingCode::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    // ExportSegments::class,
                    // LogSegmentStatistics::class,
                    // LogEntityStatistics::class,
                    // InstallCommand::class,
                    StartCommand::class,
                    CreatePartitions::class,
                    DropPartitions::class,
                    ExportSegments::class,
                    RecordObjectAttributes::class,
                    RecordSegmentStatistics::class,
                    ProcessSegmentEvents::class,
                ],
            );
        }

        /**
         * Load Migrations to update the database
         */
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /**
         * Load resources used by this package
         */
        Blade::component('blank-layout', BlankLayout::class);

        /**
         * Publish resources used by this package
         */
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cascade');

        /**
         * Publish Config file
         */
        $this->publishes(
            [
                __DIR__.'/../config/cascade.php' => config_path(
                    'cascade.php'
                ),
            ],
        );

        /**
         * Load Routes
         */
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}

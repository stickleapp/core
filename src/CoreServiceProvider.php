<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use StickleApp\Core\Commands\Configure;
use StickleApp\Core\Commands\CreatePartitions;
use StickleApp\Core\Commands\DropPartitions;
use StickleApp\Core\Commands\ExportSegments;
use StickleApp\Core\Commands\ProcessSegmentEvents;
use StickleApp\Core\Commands\RecordObjectAttributes;
use StickleApp\Core\Commands\RecordSegmentStatistics;
use StickleApp\Core\Components\BlankLayout;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Middleware\InjectJavascriptTrackingCode;
use StickleApp\Core\Middleware\RequestLogger;
use StickleApp\Core\Models\ObjectAttribute;
use StickleApp\Core\Observers\ObjectAttributeObserver;
use StickleApp\Core\Repositories\PostgresAnalyticsRepository;

final class CoreServiceProvider extends ServiceProvider
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

        if (config('stickle.tracking.server.loadMiddleware') === true) {
            $kernel->pushMiddleware(RequestLogger::class);
        }

        if (config('stickle.tracking.client.loadMiddleware') === true) {
            $kernel->pushMiddleware(InjectJavascriptTrackingCode::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    CreatePartitions::class,
                    DropPartitions::class,
                    ExportSegments::class,
                    RecordObjectAttributes::class,
                    RecordSegmentStatistics::class,
                    ProcessSegmentEvents::class,
                    Configure::class,
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'stickle');

        /**
         * Publish Config file
         */
        $this->publishes(
            [
                __DIR__.'/../config/stickle.php' => config_path(
                    'stickle.php'
                ),
            ],
        );

        /**
         * Load Routes
         */
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use StickleApp\Core\Commands\CreatePartitionsCommand;
use StickleApp\Core\Commands\DropPartitionsCommand;
use StickleApp\Core\Commands\ExportSegmentsCommand;
use StickleApp\Core\Commands\InstallCommand;
use StickleApp\Core\Commands\ProcessSegmentEventsCommand;
use StickleApp\Core\Commands\RecordModelAttributesCommand;
use StickleApp\Core\Commands\RecordModelRelationshipStatisticsCommand;
use StickleApp\Core\Commands\RecordSegmentStatisticsCommand;
use StickleApp\Core\Commands\RollupSessionsCommand;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Middleware\InjectJavascriptTrackingCode;
use StickleApp\Core\Middleware\RequestLogger;
use StickleApp\Core\Models\ModelAttributes;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Observers\ModelAttributesObserver;
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
        $this->app->bind(AnalyticsRepositoryContract::class, PostgresAnalyticsRepository::class);
    }

    public function boot(Kernel $kernel): void
    {
        $kernel = $this->app->make(Kernel::class);

        ModelAttributes::observe(ModelAttributesObserver::class);

        /** Allows URLs using Segment Class instead of ID */
        Route::bind('segment', function (string $value) {

            if (is_numeric($value)) {
                return Segment::findOrFail($value);
            }

            return Segment::where('as_class', $value)->firstOrFail();
        });

        if (config('stickle.tracking.server.loadMiddleware') === true) {
            $kernel->pushMiddleware(RequestLogger::class);
        }

        if (config('stickle.tracking.client.loadMiddleware') === true) {
            $kernel->pushMiddleware(InjectJavascriptTrackingCode::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    RollupSessionsCommand::class,
                    CreatePartitionsCommand::class,
                    DropPartitionsCommand::class,
                    ExportSegmentsCommand::class,
                    RecordModelAttributesCommand::class,
                    RecordSegmentStatisticsCommand::class,
                    RecordModelRelationshipStatisticsCommand::class,
                    ProcessSegmentEventsCommand::class,
                    InstallCommand::class,
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
        Blade::componentNamespace('StickleApp\\Core\\Views\\Components', 'stickle');

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
         * Publish Assets
         */
        $this->publishes(
            [
                __DIR__.'/../build' => public_path('vendor/stickleapp/core'),
            ],
            'package-assets'
        );

        /**
         * Load Routes
         */
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');

        // if (file_exists($buildPath = $this->app->basePath('public/build/manifest.json'))) {
        //     Vite::useManifestFromBuildDirectory('build');
        // }
    }
}

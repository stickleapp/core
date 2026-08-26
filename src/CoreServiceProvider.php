<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Override;
use StickleApp\Core\Commands\CreatePartitionsCommand;
use StickleApp\Core\Commands\DevScheduleCommand;
use StickleApp\Core\Commands\DropPartitionsCommand;
use StickleApp\Core\Commands\ExportSegmentsCommand;
use StickleApp\Core\Commands\InstallCommand;
use StickleApp\Core\Commands\ProcessSegmentEventsCommand;
use StickleApp\Core\Commands\RecordModelAttributesCommand;
use StickleApp\Core\Commands\RecordModelRelationshipStatisticsCommand;
use StickleApp\Core\Commands\RecordSegmentStatisticsCommand;
use StickleApp\Core\Commands\RollupRequestsCommand;
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
    #[Override]
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);

        /**
         * Bind the Analytics Repository
         */
        $this->app->bind(
            AnalyticsRepositoryContract::class,
            PostgresAnalyticsRepository::class,
        );

        /**
         * Resolve Stickle's own assets.
         *
         * A dedicated Vite instance, not the application's, so the host app's
         * `@vite` is untouched. The build directory matches where
         * `vendor:publish --tag=package-assets` copies the assets, and the hot
         * file matches `hotFile` in vite.config.js -- so HMR during package
         * development and published assets in an installed app resolve through
         * one code path with no environment branching.
         *
         * If the assets have not been published this throws
         * ViteManifestNotFoundException naming the exact missing path, rather
         * than silently emitting a URL that 404s.
         */
        $this->app->singleton('stickle.vite', fn (): Vite => (new Vite)
            ->useBuildDirectory('vendor/stickleapp/core')
            ->useHotFile(public_path('vendor/stickleapp/core/hot'))
            ->withEntryPoints([
                'resources/css/app.css',
                'resources/js/app.js',
            ]));
    }

    public function boot(Kernel $kernel): void
    {
        $kernel = $this->app->make(Kernel::class);

        if (config('stickle.tracking.server.modelAttributes') === true) {
            ModelAttributes::observe(ModelAttributesObserver::class);
        }

        /** Allows URLs using Segment Class instead of ID */
        Route::bind('segment', function (string $value) {
            if (is_numeric($value)) {
                return Segment::query()->findOrFail($value);
            }

            return Segment::query()->where('as_class', $value)->firstOrFail();
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
                    RollupRequestsCommand::class,
                    RollupSessionsCommand::class,
                    CreatePartitionsCommand::class,
                    DropPartitionsCommand::class,
                    ExportSegmentsCommand::class,
                    RecordModelAttributesCommand::class,
                    RecordSegmentStatisticsCommand::class,
                    RecordModelRelationshipStatisticsCommand::class,
                    ProcessSegmentEventsCommand::class,
                    InstallCommand::class,
                    DevScheduleCommand::class,
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
        Blade::componentNamespace(
            'StickleApp\\Core\\Views\\Components',
            'stickle',
        );

        /**
         * Publish resources used by this package
         */
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'stickle');

        /**
         * Publish Config file
         */
        $this->publishes([
            __DIR__.'/../config/stickle.php' => config_path('stickle.php'),
        ]);

        /**
         * Publish Assets
         *
         * Tagged 'laravel-assets' as well as 'package-assets', because the
         * default Laravel application's composer.json runs
         * `vendor:publish --tag=laravel-assets --force` on post-update-cmd.
         * Without that tag an installing app has to add its own publish step,
         * and a stale copy survives an upgrade -- or, on a first install,
         * nothing is copied at all and the scoped Vite instance registered
         * above throws ViteManifestNotFoundException on every UI page. Horizon
         * and Telescope dual-tag their assets for the same reason.
         */
        $this->publishes(
            [
                __DIR__.'/../public/build' => public_path(
                    'vendor/stickleapp/core',
                ),
                // favicon.svg lives outside public/build, so the directory copy
                // above misses it and the layout's icon link 404s without this.
                __DIR__.'/../public/favicon.svg' => public_path(
                    'vendor/stickleapp/core/favicon.svg',
                ),
            ],
            ['package-assets', 'laravel-assets'],
        );

        /**
         * Load Routes
         */
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');
    }
}

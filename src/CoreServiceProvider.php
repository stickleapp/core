<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use StickleApp\Core\Commands\ConfigureCommand;
use StickleApp\Core\Commands\CreatePartitionsCommand;
use StickleApp\Core\Commands\DropPartitionsCommand;
use StickleApp\Core\Commands\ExportSegmentsCommand;
use StickleApp\Core\Commands\ProcessSegmentEventsCommand;
use StickleApp\Core\Commands\RecordObjectAttributesCommand;
use StickleApp\Core\Commands\RecordSegmentStatisticsCommand;
use StickleApp\Core\Commands\RecordObjectStatisticsCommand;
use StickleApp\Core\Commands\RollupSessionsCommand;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Middleware\InjectJavascriptTrackingCode;
use StickleApp\Core\Middleware\RequestLogger;
use StickleApp\Core\Models\ObjectAttribute;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Observers\ObjectAttributeObserver;
use StickleApp\Core\Repositories\PostgresAnalyticsRepository;
use StickleApp\Core\Views\Components\Demo\Layouts\DefaultLayout as DemoDefaultLayout;
use StickleApp\Core\Views\Components\UI\Chartlists\ModelChartlist;
use StickleApp\Core\Views\Components\UI\Chartlists\ModelsChartlist;
use StickleApp\Core\Views\Components\UI\Charts\ModelChart;
use StickleApp\Core\Views\Components\UI\Charts\ModelsChart;
use StickleApp\Core\Views\Components\UI\Charts\Primatives\InfoChart;
use StickleApp\Core\Views\Components\UI\Charts\Primatives\LineChart;
use StickleApp\Core\Views\Components\UI\Charts\SegmentChart;
use StickleApp\Core\Views\Components\UI\Layouts\DefaultLayout as UIDefaultLayout;
use StickleApp\Core\Views\Components\UI\Tables\ModelsTable;
use StickleApp\Core\Views\Components\UI\Tables\Primatives\PaginationSimple;
use StickleApp\Core\Views\Components\UI\Tables\SegmentTable;
use StickleApp\Core\Views\Components\UI\Timelines\EventTimeline;

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

        ObjectAttribute::observe(ObjectAttributeObserver::class);

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
                    RecordObjectAttributesCommand::class,
                    RecordSegmentStatisticsCommand::class,
                    RecordObjectStatisticsCommand::class,
                    ProcessSegmentEventsCommand::class,
                    ConfigureCommand::class,
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
        Blade::component('stickle-demo-default-layout', DemoDefaultLayout::class);
        Blade::component('stickle-ui-default-layout', UIDefaultLayout::class);
        Blade::component('stickle-segment-chart', SegmentChart::class);
        Blade::component('stickle-segment-table', SegmentTable::class);
        Blade::component('stickle-models-table', ModelsTable::class);
        Blade::component('stickle-events-timeline', EventTimeline::class);
        Blade::component('stickle-pagination-simple', PaginationSimple::class);
        Blade::component('stickle-chartlists-models', ModelsChartlist::class);
        Blade::component('stickle-chartlists-model', ModelChartlist::class);
        Blade::component('stickle-charts-models', ModelsChart::class);
        Blade::component('stickle-charts-model', ModelChart::class);
        Blade::component('stickle-charts-primatives-line', LineChart::class);
        Blade::component('stickle-charts-primatives-info', InfoChart::class);

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
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');

        // if (file_exists($buildPath = $this->app->basePath('public/build/manifest.json'))) {
        //     Vite::useManifestFromBuildDirectory('build');
        // }
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use StickleApp\Core\CoreServiceProvider;

use function Orchestra\Testbench\artisan;
use function Orchestra\Testbench\workbench_path;

class TestCase extends Orchestra
{
    protected static $latestResponse;

    protected $tablePrefix;

    /**
     * Publishing is per-process, not per-test: the files do not change between
     * tests and the copy is not free.
     */
    private static bool $assetsPublished = false;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->publishPackageAssets();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'StickleApp\\Core\\Laravelstickle\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

    }

    /**
     * Put the package's built assets where the UI views expect to find them.
     *
     * Those views render through app('stickle.vite'), which resolves against a
     * manifest in the application's public/vendor/stickleapp/core. Nothing in a
     * checkout puts it there. `npm run build:publish` does, so a developer who
     * has run it sees these tests pass while CI -- which never does -- got
     * ViteManifestNotFoundException from every test that renders a page.
     *
     * public/build is committed, so this is a file copy rather than a build,
     * and it keeps the suite runnable from any checkout state instead of
     * depending on which npm scripts happen to have been run.
     */
    private function publishPackageAssets(): void
    {
        if (self::$assetsPublished) {
            return;
        }

        Artisan::call('vendor:publish', [
            '--tag' => 'package-assets',
            '--force' => true,
        ]);

        self::$assetsPublished = true;
    }

    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'pgsql');

        // Set Stickle configuration from environment variables
        config()->set('stickle.namespaces.models', env('STICKLE_NAMESPACES_MODELS'));
        config()->set('stickle.namespaces.segments', env('STICKLE_NAMESPACES_SEGMENTS'));
        config()->set('stickle.namespaces.listeners', env('STICKLE_NAMESPACES_LISTENERS'));
        config()->set('stickle.database.tablePrefix', env('STICKLE_DATABASE_TABLE_PREFIX'));

        // The package config is only published (not merged) by CoreServiceProvider, so
        // config defaults are absent under testbench. Enable model-attribute tracking so
        // the ModelAttributesObserver is registered, matching a real (published) install.
        config()->set('stickle.tracking.server.modelAttributes', true);

        // This fixes a bug in GitHub Actions runner. But find out why it's needed.
        config()->set('stickle.broadcasting.channels.object', 'stickle.object.%s.%s');

        // $migration = include __DIR__.'/../database/migrations/initial_structure.php';
        // $migration->up();

        // The package denies until the application defines this. The suite exercises
        // the routes themselves, so it stands in as that application. Tests that need
        // the unconfigured state call withoutStickleGate().
        Gate::define('viewStickle', fn ($user = null): bool => true);
    }

    // /**
    //  * Define database migrations.
    //  *
    //  * @return void
    //  */
    // protected function defineDatabaseMigrations()
    // {
    //     $this->loadMigrationsFrom(
    //         workbench_path('database/migrations')
    //     );
    // }

    protected function defineDatabaseMigrations()
    {
        // Load Laravel's default migrations
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations',
            // workbench_path('database/migrations'),
        );

        $this->loadMigrationsFrom(
            // __DIR__.'/../database/migrations',
            workbench_path('database/migrations'),
        );

        // // Load your custom migrations
        // $this->loadMigrationsFrom(workbench_path('database/migrations'));

        $this->tablePrefix = config('stickle.database.tablePrefix');

        $date = now()->subWeeks(1);

        // Run artisan command to generate partitions
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_5min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1hr public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1day public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}sessions_rollup_1day public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}model_attribute_audit public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}model_relationship_statistics public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}segment_statistics public week '{$date}' 2");

    }
}

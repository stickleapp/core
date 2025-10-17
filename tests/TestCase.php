<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests;

use Override;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use StickleApp\Core\CoreServiceProvider;

use function Orchestra\Testbench\artisan;
use function Orchestra\Testbench\workbench_path;

class TestCase extends Orchestra
{
    protected static $latestResponse;

    protected $tablePrefix;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'StickleApp\\Core\\Laravelstickle\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

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

        // This fixes a bug in GitHub Actions runner. But find out why it's needed.
        config()->set('stickle.broadcasting.channels.object', 'stickle.object.%s.%s');

        // $migration = include __DIR__.'/../database/migrations/initial_structure.php';
        // $migration->up();
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

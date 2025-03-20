<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use StickleApp\Core\CoreServiceProvider;

use function Orchestra\Testbench\artisan;
use function Orchestra\Testbench\workbench_path;

class TestCase extends Orchestra
{
    protected static $latestResponse = null;

    protected $tablePrefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tablePrefix = config('stickle.database.tablePrefix');

        $date = now()->subWeeks(1);

        // Run artisan command to generate partitions
        Artisan::call("stickle:create-partitions {$this->tablePrefix}events public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}events_rollup_1min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}events_rollup_5min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}events_rollup_1hr public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}events_rollup_1day public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_5min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1hr public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}requests_rollup_1day public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}sessions_rollup_1day public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$this->tablePrefix}object_attributes_audit public week '{$date}' 2");

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'StickleApp\\Core\\Laravelstickle\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

    }

    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'pgsql');
        $app['config']->set('stickle.broadcasting.channels.object', 'stickle.object.%s.%s');

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
    }
}

<?php

namespace StickleApp\Core\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;
use StickleApp\Core\CoreServiceProvider;

use function Orchestra\Testbench\workbench_path;

#[WithMigration]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

        // $migration = include __DIR__.'/../database/migrations/initial_structure.php';
        // $migration->up();
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }
}

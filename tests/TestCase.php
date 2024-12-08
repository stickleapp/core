<?php

namespace Dclaysmith\LaravelCascade\Tests;

use Dclaysmith\LaravelCascade\Providers\LaravelCascadeServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;

#[WithMigration]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Dclaysmith\\LaravelCascade\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCascadeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'pgsql');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-cascade_table.php.stub';
        $migration->up();
        */
    }
}

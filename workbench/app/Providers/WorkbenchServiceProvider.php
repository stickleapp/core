<?php

namespace Workbench\App\Providers;

// use Dclaysmith\LaravelCascade\Commands\CreatePartitions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::view('/', 'welcome');

        // if ($this->app->runningInConsole()) {
        //     $this->commands(
        //         commands: [
        //             CreatePartitions::class,
        //         ],
        //     );
        // }
    }
}

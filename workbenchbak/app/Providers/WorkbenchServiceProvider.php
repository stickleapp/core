<?php

namespace Workbench\App\Providers;

// use Dclaysmith\LaravelCascade\Commands\CreatePartitions;
// use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SplFileInfo;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::view('/', 'welcome');

        /**
         * I don't love this but it's the only way to get the class names to be discovered
         */
        // DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file, $basePath) {
        //     $basePath = env('APP_BASE_PATH');
        //     $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);
        //     $class = Str::replaceFirst('app', 'App', $class);

        //     return ucfirst(Str::camel(str_replace(
        //         [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).'\\'],
        //         ['\\', app()->getNamespace()],
        //         ucfirst(Str::replaceLast('.php', '', $class))
        //     )));
        // });
    }
}

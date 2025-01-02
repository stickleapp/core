<?php

namespace Workbench\App\Providers;

// use Dclaysmith\LaravelCascade\Commands\CreatePartitions;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SplFileInfo;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        // DiscoverEvents::$guessClassNamesUsingCallback = function (SplFileInfo $file, $basePath) {
        //     // return 'Workbench\\App\\Listeners\\'.str_replace(
        //     //     ['/', '.php'],
        //     //     ['\\', ''],
        //     //     Str::after($file->getPathname(), $namespace)
        //     // );
        //     dd('donkey');
        // };
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

        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file, $basePath) {

            $basePath = '/Users/dclaysmith/Projects/LaravelCascade';

            $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

            $class = Str::replaceFirst('app', 'App', $class);

            return ucfirst(Str::camel(str_replace(
                [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).'\\'],
                ['\\', app()->getNamespace()],
                ucfirst(Str::replaceLast('.php', '', $class))
            )));
        });
    }
}

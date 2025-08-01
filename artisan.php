#!/usr/bin/env php
<?php

use Filament\Commands\MakePanelCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel (if used inside a Laravel app)
// $app = require_once __DIR__.'/bootstrap/app.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    ConsoleKernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    ConsoleKernel::class
);

// $app->singleton(
//     Illuminate\Contracts\Debug\ExceptionHandler::class,
//     App\Exceptions\Handler::class
// );
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

// Ensure an argument is passed
// if ($argc < 2) {
//     echo "Usage: artisan-runner <command> [options]\n";
//     exit(1);
// }

// Extract command and options
$command = $argv[1];
$options = array_slice($argv, 2);

// Run Artisan command
$exitCode = Artisan::call(MakePanelCommand::class, $options);

// Print the output
echo Artisan::output();

exit($exitCode);

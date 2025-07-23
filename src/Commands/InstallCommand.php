<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\spin;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stickle:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create your Stickle configuration.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        Log::info(self::class, $this->arguments());

        info('🧙‍♂️ Welcome to the Stickle configuration wizard!');

        pause('Ready? Press ENTER key to continue...');

        info('Stickle works great with Laravel Reverb for real-time updates!');

        note('Laravel Reverb is an official WebSocket server that enables real-time communication.');

        if (confirm(
            label: 'Would you like to install Laravel Reverb?'
        )) {

            $this->info('Installing Laravel Reverb...');

            spin(
                fn () => $this->installReverb(),
                'Installing Laravel Reverb...'
            );

            info("\nLaravel Reverb installed successfully!");

            note('Start the WebSocket server with: php artisan reverb:start');

        } else {

            info('You can install Laravel Reverb later by running:');

            note('php artisan install:broadcasting');

            note('Learn more at: https://laravel.com/docs/reverb');
        }

        if (confirm(
            label: 'Would you like to run the migrations now?'
        )) {

            spin(
                fn () => $this->runMigrations(),
                'Running migrations...'
            );

            info("\nMigrations ran successfully!");

        } else {

            info('You can run the migrations later by running:');

            note('php artisan migrate');
        }

        if (confirm(
            label: 'Would you like to publish Stickle files?'
        )) {

            $this->call('vendor:publish', [
                '--provider' => 'StickleApp\Core\CoreServiceProvider',
            ]);

            info('Stickle files published successfully!');

        } else {

            info('You can publish the Stickle file later by running:');

            note('php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"');
        }
    }

    private function installReverb()
    {
        $this->call('install:broadcasting');
    }

    private function runMigrations()
    {
        $this->call('migrate');
    }
}

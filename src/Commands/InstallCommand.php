<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\CoreServiceProvider;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

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

        $labels = [
            'STICKLE_NAMESPACES_MODELS' => 'Models Path',
            'STICKLE_NAMESPACES_LISTENERS' => 'Listeners Path',
            'STICKLE_NAMESPACES_SEGMENTS' => 'Segments Path',
            'STICKLE_DATABASE_SCHEMA' => 'Database Schema',
            'STICKLE_DATABASE_TABLE_PREFIX' => 'Table Prefix',
            'STICKLE_DATABASE_ENABLE_PARTITIONS' => 'Enable Partitioning',
            'STICKLE_FILESYSTEM_DISK_EXPORTS' => 'Storage Disk',
            'STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE' => 'Server Load Middleware',
            'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED' => 'Track Authentication Events',
            'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE' => 'Client Load Middleware',
            'STICKLE_WEB_PREFIX' => 'StickleUI Path',
            'STICKLE_WEB_MIDDLEWARE' => 'Web Middleware',
            'STICKLE_API_PREFIX' => 'API Prefix',
            'STICKLE_API_MIDDLEWARE' => 'API Middleware',
            'interval' => 'Default Process Interval (minutes)',
            'STICKLE_FREQUENCY_EXPORT_SEGMENTS' => 'Export Segments Frequency (minutes)',
        ];

        $settings = form()
            ->info('🧙‍♂️ Welcome to the Stickle configuration wizard!')
            ->note("We'll ask you a few questions help you configure Stickle.")
            ->pause('Ready? Press ENTER key to continue...')
            ->note('Stickle tracks your \'customers\'.')
            ->note('This can mean different things in different apps.')
            ->note('90% of the time that means your `User` model but it can also include other models such as `Company`, `Organization`, or `Account`.')
            ->text(
                label: 'Where do you place your laravel models?',
                default: 'App\Models',
                required: true,
                validate: ['STICKLE_NAMESPACES_MODELS' => 'string|required'],
                hint: 'Full namespace, e.g. `App\Models`',
                name: 'STICKLE_NAMESPACES_MODELS'
            )
            ->note('Stickle makes it super simple to react to client-side events using server-side code.')
            ->text(
                label: 'Where do you place your event listeners?',
                default: 'App\Listeners',
                required: true,
                validate: ['STICKLE_NAMESPACES_LISTENERS' => 'string|required'],
                hint: 'Full namespace, e.g. `App\Listeners`',
                name: 'STICKLE_NAMESPACES_LISTENERS'
            )
            ->note('If you can use Eloquent then you can build powerful Customer segments that Stickle will track for you.')
            ->warning('If if you can\'t write an Eloquent query, you probably should stop now but, hey, that\'s your call.')
            ->pause('Did I scare you away? No? Press ENTER key to continue...')
            ->text(
                label: 'Where will you place your Stickle Segments?',
                default: 'App\Segments',
                required: true,
                validate: ['STICKLE_NAMESPACES_SEGMENTS' => 'string|required'],
                hint: 'Full namespace, e.g. `App\Segments`',
                name: 'STICKLE_NAMESPACES_SEGMENTS'
            )
            ->note('Stickle will need access to your primary database.')
            ->warning('It **will** query existing tables and create new ones.')
            ->warning('It **won\'t** modify or delete any of your existing tables.')
            ->suggest(
                label: 'Which database schema should be used?',
                options: fn () => collect(config('database.connections'))
                    ->filter(fn ($connection): bool => $connection['driver'] === 'pgsql')
                    ->map(fn ($connection, $name): mixed => $connection['schema'] ?? 'public')
                    ->unique()
                    ->values()
                    ->toArray(),
                default: 'public',
                required: true,
                validate: ['connection' => 'string|required'],
                name: 'STICKLE_DATABASE_SCHEMA'
            )
            ->note('Stickle can prefix the name of tables it creates with a string to help keep things organized and prevent name collision.')
            ->text(
                label: 'Would you like to prefix your Stickle table names with a short string?',
                default: 'stc_',
                validate: ['STICKLE_DATABASE_TABLE_PREFIX' => 'string|min:0|max:10'],
                name: 'STICKLE_DATABASE_TABLE_PREFIX'
            )
            ->confirm(
                label: 'Would you like to use partitioning?',
                default: true,
                yes: 'Yes',
                no: 'No',
                required: false,
                hint: 'PostgreSQL only - If you have a high volume of events and page views, this make Stickle more performant.',
                name: 'STICKLE_DATABASE_ENABLE_PARTITIONS'
            )
            ->note('Stickle needs access to a disk to store files for loading large data sets.')
            ->suggest(
                label: 'What storage disk should be used for data exports?',
                options: fn (): array => array_keys(config('filesystems.disks')),
                default: 'local',
                required: true,
                validate: ['STICKLE_FILESYSTEM_DISK_EXPORTS' => 'string'],
                name: 'STICKLE_FILESYSTEM_DISK_EXPORTS'
            )
            ->note('Stickle can track every request received using server-side middleware.')
            ->confirm(
                label: 'Do you want to track server requests using middleware?',
                default: true,
                yes: 'Yes',
                no: 'No',
                required: true,
                name: 'STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE'
            )
            ->note('Stickle can track Laravel authentication events such as logins, logouts, password resets, etc.')
            ->confirm(
                label: 'Do you want to track Laravel authentication events?',
                default: true,
                yes: 'Yes',
                no: 'No',
                required: true,
                name: 'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED'
            )
            ->note('Stickle can track insert a small Javascript snippet that will track user events and page views.')
            ->note('You can further configure this tracking code to track custom client-side events.')
            ->confirm(
                label: 'Do you want to track client-side requests using Javascript?',
                default: true,
                yes: 'Yes',
                no: 'No',
                required: true,
                name: 'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE'
            )
            ->note('StickleUI gives you visual access to your data.')
            ->note('By default, it is available at `/stickle` but you can change it.')
            ->text(
                label: 'What path would you like to use for accessing StickleUI?',
                default: 'stickle',
                required: true,
                validate: ['STICKLE_WEB_PREFIX' => 'string'],
                name: 'STICKLE_WEB_PREFIX'
            )
            ->note('Stickle will apply middleware you require to the web routes.')
            ->text(
                label: 'What middleware would you like to apply to the web routes?',
                default: 'web',
                validate: ['STICKLE_WEB_MIDDLEWARE' => 'string'],
                name: 'STICKLE_WEB_MIDDLEWARE'
            )
            ->note('Stickle exposes some API routes used by the UI.')
            ->note('We prefix the routes (`api/stickle`) to distinguish them from your other routes.')
            ->text(
                label: 'What prefix would you like to use for the API routes?',
                default: 'api/stickle',
                validate: ['STICKLE_API_PREFIX' => 'string'],
                name: 'STICKLE_API_PREFIX'
            )
            ->note('Stickle will apply middleware you require to the API routes.')
            ->text(
                label: 'What middleware would you like to apply to the API routes?',
                default: 'api',
                validate: ['STICKLE_API_MIDDLEWARE' => 'string'],
                name: 'STICKLE_API_MIDDLEWARE'
            )
            ->note('Stickle processes some items in real-time but other items are refreshed on a schedule.')
            ->note('You can specify how frequently data should be refreshed.')
            ->text(
                label: 'How long should we wait before refreshing data?',
                default: '360',
                required: true,
                validate: ['interval' => 'required|int|min:1'],
                hint: 'Every X minutes',
                name: 'interval'
            )
            ->info('Please review your settings.')
            ->note('You can change any of these settings in config/stickle.php.')
            ->add(function ($settings) use ($labels) {
                $rows = collect($settings)
                    ->reject(fn ($value, $key): bool => is_numeric($key))
                    ->map(fn ($value, $key): array => [
                        'label' => (string) $labels[$key] ?? $key,
                        'value' => (string) $value,
                    ]);

                return table(
                    headers: ['Setting', 'Value'],
                    rows: $rows,
                );
            })->submit();

        if (confirm(
            label: 'Are you ready to publish the configuration file?',
            hint: 'This will run: php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"'
        )) {

            // Clear out the notes() etc, with numerical indexes
            $settings = collect($settings)->reject(fn ($value, $key): bool => is_numeric($key))->toArray();

            $settings = $this->formatSettings($settings);

            $this->writeEnvFile($settings);

            $this->call('config:clear');

            $this->call('vendor:publish', [
                '--force' => true,
                '--provider' => CoreServiceProvider::class,
            ]);

            info('Configuration published successfully!');

            note('You can find it at: config/stickle.php');
        } else {

            info('You can publish the configuration later by running:');

            note('php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"');
        }

        info('Stickle works great with Laravel Reverb for real-time updates!');

        note('Laravel Reverb is an official WebSocket server that enables real-time communication.');

        note('When used with Stickle, you\'ll get instant updates in the UI whenever events occur.');

        if (confirm(
            label: 'Would you like to install Laravel Reverb?'
        )) {

            $this->info('Installing Laravel Reverb...');

            spin(
                fn () => $this->installReverb(),
                'Installing Laravel Reverb...'
            );

            info('Laravel Reverb installed successfully!');

            note('Start the WebSocket server with: php artisan reverb:start');

        } else {

            info('You can install Laravel Reverb later by running:');

            note('php artisan install:broadcasting');

            note('Learn more at: https://laravel.com/docs/reverb');
        }

        $this->call('config:cache');

        if (confirm(
            label: 'Would you like to run the migrations now?'
        )) {

            spin(
                fn () => $this->runMigrations(),
                'Running migrations...'
            );

            info('Migrations ran successfully!');

        } else {

            info('You can run the migrations later by running:');

            note('php artisan migrate');
        }

        if (confirm('Would you like to star our repo on GitHub?')) {

            $repoUrl = 'https://github.com/stickleapp/core';

            if (PHP_OS_FAMILY === 'Darwin') {
                exec("open {$repoUrl}");
            }
            if (PHP_OS_FAMILY === 'Windows') {
                exec("start {$repoUrl}");
            }
            if (PHP_OS_FAMILY === 'Linux') {
                exec("xdg-open {$repoUrl}");
            }
        }

        outro('You are all set! Let us know what you build with Stickle!');

    }

    private function installReverb(): void
    {
        $this->call('install:broadcasting');
    }

    private function runMigrations(): void
    {
        $this->call('migrate');
    }

    /**
     * Format the settings array.
     */
    private function formatSettings(array $settings): array
    {

        $settings['STICKLE_FREQUENCY_EXPORT_SEGMENTS'] = $settings['interval'] ?? 360;
        $settings['STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES'] = $settings['interval'] ?? 360;
        $settings['STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE'] = $settings['interval'] ?? 360;
        $settings['STICKLE_FREQUENCY_RECORD_SEGMENT_STATISTICS'] = $settings['interval'] ?? 360;

        unset($settings['interval']);

        $settings['STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE'] = Arr::get($settings, 'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE', false) ? 'Authenticated,CurrentDeviceLogout,Login,Logout,OtherDeviceLogout,PasswordReset,Registered,Validated,Verified' : '';

        return $settings;
    }

    private function writeEnvFile(array $settings): void
    {
        // Read the stub file
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            alert('.env file not found at '.$envPath);

            return;
        }

        // Read the current .env file
        $envContent = file_get_contents($envPath);

        // Process each setting
        foreach ($settings as $key => $value) {
            // Format the value for .env file
            $envValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;

            // If value contains spaces or special characters, wrap it in quotes
            if (is_string($value) && preg_match('/\s|[^a-zA-Z0-9_.]/', (string) $envValue)) {
                $envValue = '"'.str_replace(['"', '\\'], ['\"', '\\\\\\\\'], $envValue).'"';
            }

            // Check if the key already exists in the .env file
            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                // Replace existing value
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$envValue}", $envContent);
            } else {
                // Append to the end
                $envContent .= PHP_EOL."{$key}={$envValue}";
            }
        }

        // Write the updated content back to the .env file
        file_put_contents($envPath, $envContent);

        info('Environment variables updated in .env file.');
    }
}

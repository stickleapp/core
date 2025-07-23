<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\form;
use function Laravel\Prompts\spin;

class ConfigureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stickle:configure';

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
            'modelsPath' => 'Models Path',
            'listenersPath' => 'Listeners Path',
            'segmentsPath' => 'Segments Path',
            'dbConnection' => 'Database Connection',
            'tablePrefix' => 'Table Prefix',
            'enablePartitioning' => 'Enable Partitioning',
            'storageDisk' => 'Storage Disk',
            'serverLoadMiddleware' => 'Server Load Middleware',
            'trackAuthenticationEvents' => 'Track Authentication Events',
            'clientLoadMiddleware' => 'Client Load Middleware',
            'webPrefix' => 'StickleUI Path',
            'apiPrefix' => 'API Prefix',
            'interval' => 'Default Process Interval (minutes)',
        ];

        $settings = form()
            ->info('🧙‍♂️ Welcome to the Stickle configuration wizard!')
            ->note("We'll ask you a few questions help you configure Stickle.")
            ->pause('Ready? Press ENTER key to continue...')
            ->note('Stickle tracks your \'customers\'.')
            ->note('This can mean different things in different apps.')
            ->note('90% of the time that means your `User` model but it can also include other models such as `Company`, `Organization`, or `Account`.')
            ->text(
                name: 'modelsPath',
                label: 'Where do you place your laravel models?',
                validate: ['modelsPath' => 'string|required'],
                required: true,
                hint: 'Full namespace, e.g. `App\Models`',
                default: 'App\Models'
            )
            ->note('Stickle makes it super simple to react to client-side events using server-side code.')
            ->text(
                name: 'listenersPath',
                label: 'Where do you place your event listeners?',
                validate: ['listenersPath' => 'string|required'],
                required: true,
                hint: 'Full namespace, e.g. `App\Listeners`',
                default: 'App\Listeners'
            )
            ->note('If you can use Eloquent then you can build powerful Customer segments that Stickle will track for you.')
            ->warning('If if you can\'t write an Eloquent query, you probably should stop now but, hey, that\'s your call.')
            ->pause('Did I scare you away? No? Press ENTER key to continue...')
            ->text(
                name: 'segmentsPath',
                label: 'Where will you place your Stickle Segments?',
                validate: ['segmentsPath' => 'string|required'],
                required: true,
                hint: 'Full namespace, e.g. `App\Segments`',
                default: 'App\Segments'
            )
            ->note('Stickle will need access to your primary database.')
            ->warning('It **will** query existing tables and create new ones.')
            ->warning('It **won\'t** modify or delete any of your existing tables.')
            ->suggest(
                name: 'dbConnection',
                label: 'Which database connection should be used? We aren\'t running migrations yet, so you can change this later.',
                validate: ['connection' => 'string|required'],
                required: true,
                default: 'pgsql',
                options: fn() => array_keys(config('database.connections'))
            )
            ->note('Stickle can prefix the name of tables it creates with a string to help keep things organized and prevent name collision.')
            ->text(
                name: 'tablePrefix',
                label: 'Would you like to prefix your Stickle table names with a short string?',
                validate: ['tablePrefix' => 'string|min:0|max:10'],
                default: 'stickle_'
            )
            ->confirm(
                name: 'enablePartitioning',
                label: 'Would you like to use partitioning?',
                default: true,
                required: false,
                yes: 'Yes',
                no: 'No',
                hint: 'PostgreSQL only - If you have a high volume of events and page views, this make Stickle more performant.'
            )
            ->note('Stickle needs access to a disk to store files for loading large data sets.')
            ->suggest(
                name: 'storageDisk',
                label: 'What storage disk should be used for data exports?',
                validate: ['storageDisk' => 'string'],
                required: true,
                default: 'local',
                options: fn() => array_keys(config('filesystems.disks'))
            )
            ->note('Stickle can track every request received using server-side middleware.')
            ->confirm(
                name: 'serverLoadMiddleware',
                label: 'Do you want to track server requests using middleware?',
                default: true,
                required: true,
                yes: 'Yes',
                no: 'No'
            )
            ->note('Stickle can track Laravel authentication events such as logins, logouts, password resets, etc.')
            ->confirm(
                name: 'trackAuthenticationEvents',
                label: 'Do you want to track Laravel authentication events?',
                default: true,
                required: true,
                yes: 'Yes',
                no: 'No'
            )
            ->note('Stickle can track insert a small Javascript snippet that will track user events and page views.')
            ->note('You can further configure this tracking code to track custom client-side events.')
            ->confirm(
                name: 'clientLoadMiddleware',
                label: 'Do you want to track client-side requests using Javascript?',
                default: true,
                required: true,
                yes: 'Yes',
                no: 'No'
            )
            ->note('StickleUI gives you visual access to your data.')
            ->note('By default, it is available at `/stickle` but you can change it.')
            ->text(
                name: 'webPrefix',
                label: 'What path would you like to use for accessing StickleUI?',
                required: true,
                validate: ['webPrefix' => 'string'],
                default: 'stickle'
            )
            ->note('Stickle exposes some API routes used by the UI.')
            ->note('We prefix the routes (`api/stickle`) to distinguish them from your other routes.')
            ->text(
                name: 'apiPrefix',
                label: 'What prefix would you like to use for the API routes?',
                validate: ['apiPrefix' => 'string'],
                default: 'api/stickle'
            )
            ->note('Stickle runs a number of processes in the background to transform your data.')
            ->text(
                name: 'interval',
                label: 'How frequently would you like to run these processes (in minutes)?',
                validate: ['interval' => 'required|int|min:1'],
                required: true,
                hint: 'Every X minutes',
                default: '360'
            )
            ->info('Please review your settings.')
            ->note('You can change any of these settings in config/stickle.php.')
            ->add(function ($settings) use ($labels) {
                $rows = collect($settings)
                    ->reject(fn($value, $key) => is_numeric($key))
                    ->map(function ($value, $key) use ($labels) {
                        return [
                            'label' => (string) $labels[$key] ?? $key,
                            'value' => (string) $value,
                        ];
                    });                
                return table(
                    headers: ['Setting', 'Value'],
                    rows: $rows,
                );
            })            
            ->confirm(
                label: 'Are you happy with these values?',
                hint: 'You can change these settings later in `config/stickle.php`.',
                default: true,
                yes: 'Yes',
                no: 'No'
            )
            ->submit();
        
        // Clear out the notes() etc, with numerical indexes
        $settings = collect($settings)->reject(fn($value, $key) => is_numeric($key))->toArray();
        
        $this->writeConfigFile($settings);

        $this->call('config:clear');

        if (confirm(
            label: 'Would you like to publish the configuration file now?',
            hint: 'This will run: php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"'
        )) {            
            $this->call('vendor:publish', [
                '--force' => true,
                '--provider' => 'StickleApp\Core\CoreServiceProvider',
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
                fn() => $this->installReverb(),
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
                fn() => $this->runMigrations(),
                'Running migrations...'
            );

            info('Migrations ran successfully!');

        } else {

            info('You can run the migrations later by running:');

            note('php artisan migrate');
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

    /**
     * Format a value for insertion into a PHP configuration file.
     */
    private function formatValueForConfig(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        if (is_null($value)) {
            return 'null';
        }
        
        // Default: escape string for PHP config file
        return addslashes((string) $value);
    }   
    
    private function writeConfigFile(array $settings): void
    { 
        // Read the stub file
        $stubPath = __DIR__ . '/../../config/stickle.php.stub';
        $targetPath = __DIR__ . '/../../config/stickle.php.stub';
        
        if (!file_exists($stubPath)) {
            alert('Config stub not found at ' . $stubPath);
            return;
        }
        
        $stubContent = file_get_contents($stubPath);

        // Replace placeholders with actual values
        foreach ($settings as $key => $value) {
            
            // Format value based on type
            $formattedValue = $this->formatValueForConfig($value);
            
            // Replace the placeholder
            $stubContent = str_replace('{{' . $key . '}}', $formattedValue, $stubContent);
        }
    }
}

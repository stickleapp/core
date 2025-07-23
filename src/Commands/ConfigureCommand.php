<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

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

        $settings = [];

        info('🧙‍♂️ Welcome to the Stickle configuration wizard!');

        note("We'll ask you a few questions help you configure Stickle.");

        pause('Ready? Press ENTER key to continue...');

        $architecture = suggest(
            label: 'Which of the following best describes your application?',
            validate: ['architecture' => 'required|string'],
            options: ['Blade', 'Inertia', 'Livewire', 'SPA (Vue, React, etc.)']
        );

        // The architecture will determine settings
        switch ($architecture) {
            case 'Blade':
                note('Cool. Good old Blade 🔪. If it ain\'t broke don\'t fix it.');
                $serverLoadMiddlewareDefault = true;
                $clientLoadMiddlewareDefault = true;
                break;
            case 'Inertia':
                note('Inertia is great. A single page app without all those JS routing nightmares.');
                $serverLoadMiddlewareDefault = true;
                $clientLoadMiddlewareDefault = true;
                break;
            case 'Livewire':
                note('Livewire is awesome. It\'s like Blade, but with a sprinkle of magic.');
                $serverLoadMiddlewareDefault = true;
                $clientLoadMiddlewareDefault = true;
                break;
            case 'SPA (Vue, React, etc.)':
                note('Nothing you can\'t do with a good old Vue or React app.');
                $serverLoadMiddlewareDefault = false;
                $clientLoadMiddlewareDefault = true;
                break;
        }

        note('Stickle tracks your \'customers\'.');

        note('This can mean different things in different apps.');

        note('90% of the time that means your `User` model.');

        note('It might also include other models such as `Company`, `Organization`, or `Account`.');

        $modelsPath = text(
            label: 'Where do you place your laravel models (full namespace)?',
            validate: ['modelsPath' => 'string'],
            default: 'App\Models'
        );

        $settings[] = $this->addSetting('modelsPath', $modelsPath, 'Models Path');

        note('Stickle makes it super simple to react to client-side events using server-side code.');

        $listenersPath = text(
            label: 'Where do you place your event listeners (full namespace)?',
            validate: ['listenersPath' => 'string'],
            default: 'App\Listeners'
        );

        $settings[] = $this->addSetting('listenersPath', $listenersPath, 'Listeners Path');

        note('If you can use Eloquent then you can build powerful Customer segments that Stickle will track for you.');

        warning('If if you can\'t write an Eloquent query, you probably should stop now but hey, that\'s your call.');

        pause('Did I scare you away? No? Press ENTER key to continue...');

        $segmentsPath = text(
            label: 'Where will you place your Stickle Segments (full namespace)?',
            validate: ['segmentsPath' => 'string'],
            default: 'App\Segments'
        );

        $settings[] = $this->addSetting('segmentsPath', $segmentsPath, 'Segments Path');

        note('Stickle will need access to your primary database.');

        warning('It will query existing tables and create new ones.');

        alert('We won\'t modify any of your existing tables.');

        /** @var array<string> $connections */
        $dbConnections = config('database.connections');
        $dbConnection = suggest(
            label: 'Which database connection should be used? We aren\'t running migrations yet, so you can change this later.',
            validate: ['connection' => 'string'],
            options: array_keys($dbConnections)
        );

        $settings[] = $this->addSetting('connection', $dbConnection, 'Database Connection');

        note('Stickle can prefix the name of tables it creates with a string to help keep things organized and prevent name collision.');

        $tablePrefix = text(
            label: 'Would you like to prefix your Stickle table names with a short string?',
            validate: ['tablePrefix' => 'required|string|min:3|max:10'],
            default: 'stickle_'
        );

        $settings[] = $this->addSetting('tablePrefix', $tablePrefix, 'Table Prefix');

        if ($dbConnection === 'pgsql') {

            note('Stickle can optionally use table partitioning.');

            note('If you have a high volume of events and page views, this make Stickle more performant.');

            note('It will also allow you to remove old data without impacting database performance.');

            $enablePartitioning = confirm(
                label: 'Would you like to use partitioning?',
                default: false,
                yes: 'Yes',
                no: 'No'
            );

            $settings[] = $this->addSetting('enablePartitioning', $enablePartitioning, 'Enable Partitioning', (bool) $enablePartitioning ? 'Yes' : 'No');
        }

        note('Stickle needs access to a disk to store files for loading large data sets.');

        /** @var array<string> $disks */
        $storageDisks = config('filesystems.disks');
        $storageDisk = suggest(
            label: 'What storage disk should be used for data exports?',
            validate: ['storageDisk' => 'string'],
            options: array_keys($storageDisks)
        );

        $settings[] = $this->addSetting('storageDisk', $storageDisk, 'Storage Disk');

        note('Stickle can track every request received using server-side middleware.');

        $serverLoadMiddleware = confirm(
            label: 'Do you want to track server requests using middleware?',
            default: $serverLoadMiddlewareDefault,
            yes: 'Yes',
            no: 'No'
        );

        $settings[] = $this->addSetting('serverLoadMiddleware', $serverLoadMiddleware, 'Server Load Middleware', (bool) $serverLoadMiddleware ? 'Yes' : 'No');

        note('Stickle can track Laravel authentication events such as logins, logouts, password resets, etc.');

        $trackAuthenticationEvents = confirm(
            label: 'Do you want to track Laravel authentication events?',
            default: true,
            yes: 'Yes',
            no: 'No'
        );

        $settings[] = $this->addSetting('trackAuthenticationEvents', $trackAuthenticationEvents, 'Track  Authentication Events', (bool) $serverLoadMiddleware ? 'Yes' : 'No');

        note('Stickle can track insert a small Javascript snippet that will track user events and page views.');

        note('You can further configure this tracking code to track custom client-side events.');

        $clientLoadMiddleware = confirm(
            label: 'Do you want to track client-side requests using Javascript?',
            default: $clientLoadMiddlewareDefault,
            yes: 'Yes',
            no: 'No'
        );

        $settings[] = $this->addSetting('clientLoadMiddleware', $clientLoadMiddleware, 'Client Load Middleware', (bool) $clientLoadMiddleware ? 'Yes' : 'No');

        note('StickleUI gives you visual access to your data.');

        note('By default, it is available at `/stickle` but you can change it.');

        $webPrefix = text(
            label: 'What path would you like to use for accessing StickleUI?',
            validate: ['webPrefix' => 'string'],
            default: 'stickle'
        );

        $settings[] = $this->addSetting('webPrefix', $webPrefix, 'StickleUI Path');

        note('Stickle exposes some API routes used by the UI.');

        note('We prefix the routes (`api/stickle`) to distinguish them from your other routes.');

        $apiPrefix = text(
            label: 'What prefix would you like to use for the API routes?',
            validate: ['apiPrefix' => 'string'],
            default: 'api/stickle'
        );

        $settings[] = $this->addSetting('apiPrefix', $apiPrefix, 'API Prefix');

        note('Stickle runs a number of processes in the background to transform your data.');

        $interval = text(
            label: '🕓 How frequently would you like to run these processes (in minutes)?',
            validate: ['interval' => 'required|int|min:1'],
            default: '360'
        );

        $settings[] = $this->addSetting('interval', $interval, 'Default Process Interval (minutes)');

        info('Please review your settings.');

        note('You can change any of these settings in config/stickle.php.');

        $rows = collect($settings)->map(function ($item) {
            return [
                'label' => (string) $item['label'],
                'value' => (string) $item['displayValue'],
            ];
        });

        table(
            headers: ['Setting', 'Value'],
            rows: $rows,
        );

        $publishConfig = confirm(
            label: 'Would you publish these settings to `/stickle/config.php`?',
            default: false,
            yes: 'Yes',
            no: 'No, I\'ll change settings manually'
        );

        if ($publishConfig) {
            info('Publishing settings...');
        }

        //  Run migrations if using partioning...
    }

    /** @return array<string, mixed> */
    private function addSetting(string $key, mixed $value, string $label, ?string $displayValue = null): array
    {
        return [
            'key' => $key,
            'value' => $value,
            'displayValue' => is_null($displayValue) ? $value : $displayValue,
            'label' => $label,
        ];
    }
}

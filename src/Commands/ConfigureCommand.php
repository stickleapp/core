<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

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

        info('Welcome to the Stickle configuration wizard!');

        note("We'll ask you a few questions to get your Stickle configuration set up.");

        pause('Ready? Press ENTER key to continue...');

        $architecture = suggest(
            label: 'Which of the following best describes your application?',
            validate: ['architecture' => 'required|string'],
            options: ['Blade', 'Inertia', 'Livewire', 'SPA (Vue, React, etc.)']
        );

        // The architecture will determine settings
        if ($architecture) {

        }

        note('Stickle runs a number of processes in the background to transform your data.');

        $interval = text(
            label: 'How frequently would you like to run these processes (in minutes)?',
            validate: ['interval' => 'required|int|min:1'],
            default: '360'
        );

        $settings[] = $this->addSetting('interval', $interval, 'Default Process Interval (minutes)');

        // note('What is your user model?');

        // /** @var string $userModelDefault * */
        // $userModelDefault = config('auth.providers.users.model');
        // $userModel = text(
        //     label: 'What is your user model (full namespace)?',
        //     validate: ['userModel' => 'required|string'],
        //     default: $userModelDefault
        // );

        // $settings[] = $this->addSetting('userModel', $userModel, 'User Model');

        // note('Stickle can aggregate metrics under a group (organiation, account, tenant, etc).');

        // $groupModel = text(
        //     label: 'What is your group model (full namespace)?',
        //     validate: ['userModel' => 'string'],
        // );

        // $settings[] = $this->addSetting('groupModel', $groupModel, 'Group Model');

        $modelsPath = text(
            label: 'Where do you place your laravel models (full namespace)?',
            validate: ['modelsPath' => 'string'],
            default: 'App\Models'
        );

        $seetings[] = $this->addSetting('modelsPath', $modelsPath, 'Models Path');

        $listenersPath = text(
            label: 'Where do you place your event listeners (full namespace)?',
            validate: ['listenersPath' => 'string'],
            default: 'App\Listeners'
        );

        $settings[] = $this->addSetting('listenersPath', $listenersPath, 'Listeners Path');

        $segmentsPath = text(
            label: 'Where will you place your Stickle Segments (full namespace)?',
            validate: ['segmentsPath' => 'string'],
            default: 'App\Segments'
        );

        $settings[] = $this->addSetting('segmentsPath', $segmentsPath, 'Segments Path');

        note('Stickle will need access to your primary database. It will query existing tables and create new ones.');

        /** @var array<string> $connections */
        $dbConnections = config('database.connections');
        $dbConnection = suggest(
            label: 'Which database connection should be used?',
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

            note('Stickle can optionally use table partitioning. If you have a high volume of events and page views, this make Stickle more performant. It will also allow you to remove old data without impacting database performance.');

            $enablePartitioning = text(
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

        note('Stickle can track every request received using middleware.');

        $serverLoadMiddleware = confirm(
            label: 'Do you want to track server requests using middleware?',
            default: false,
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

        $settings[] = $this->addSetting('serverLoadMiddleware', $serverLoadMiddleware, 'Server Load Middleware', (bool) $serverLoadMiddleware ? 'Yes' : 'No');

        note('Stickle can track insert a small Javascript snippet that will track user events and page views.');

        $clientLoadMiddleware = confirm(
            label: 'Do you want to track client-side requests using Javascript?',
            default: false,
            yes: 'Yes',
            no: 'No'
        );

        $settings[] = $this->addSetting('clientLoadMiddleware', $clientLoadMiddleware, 'Client Load Middleware', (bool) $clientLoadMiddleware ? 'Yes' : 'No');

        note('StickleUI gives you visual access to your data.');

        $webPrefix = text(
            label: 'What path would you like to use for accessing StickleUI?',
            validate: ['webPrefix' => 'string'],
            default: 'stickle'
        );

        $settings[] = $this->addSetting('webPrefix', $webPrefix, 'StickleUI Path');

        note('Stickle exposes some API routes used by the UI. We prefix the routes to distinguish them from your application routes.');

        $apiPrefix = text(
            label: 'What prefix would you like to use for the API routes?',
            validate: ['apiPrefix' => 'string'],
            default: 'api/stickle'
        );

        $settings[] = $this->addSetting('apiPrefix', $apiPrefix, 'API Prefix');

        $serverLoadMiddleware = confirm(
            label: 'Do you want to track server requests using middleware?',
            default: false,
            yes: 'Yes',
            no: 'No'
        );

        info('Please review your settings.');

        note('You can change any of these settings in config/stickle.php.');

        $callback = function ($item) {
            return [
                'label' => (string) $item['label'],
                'value' => (string) $item['displayValue'],
            ];
        };

        $rows = collect($settings)->map($callback);

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

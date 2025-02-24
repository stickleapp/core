<?php

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class Configure extends Command
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
        $settings = [];

        info('Welcome to the Stickle configuration wizard!');

        note("We'll ask you a few questions to get your Stickle configuration set up.");

        pause('Shall we? Press ENTER key to continue...');

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

        note('Stickle creates tables in your database.');
        note('We can prefix these tables with a string to help keep things organized.');

        $tablePrefix = text(
            label: 'Would you like to prefix your Stickle table names with a short string?',
            validate: ['tablePrefix' => 'required|string|min:3|max:10'],
            default: 'stickle_'
        );

        $settings[] = $this->addSetting('tablePrefix', $tablePrefix, 'Table Prefix');

        note('What is your user model?');

        /** @var string $userModelDefault * */
        $userModelDefault = config('auth.providers.users.model');
        $userModel = text(
            label: 'What is your user model (full namespace)?',
            validate: ['userModel' => 'required|string'],
            default: $userModelDefault
        );

        $settings[] = $this->addSetting('userModel', $userModel, 'User Model');

        note('Stickle can aggregate metrics under a group (organiation, account, tenant, etc).');

        $groupModel = text(
            label: 'What is your group model (full namespace)?',
            validate: ['userModel' => 'string'],
        );

        $settings[] = $this->addSetting('groupModel', $groupModel, 'Group Model');

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

        note('Stickle needs access to a disk to store files for loading large data sets.');

        /** @var array<string> $disks */
        $disks = config('filesystems.disks');
        $storageDisk = suggest(
            label: 'What storage disk should be used for data exports?',
            validate: ['storageDisk' => 'string'],
            options: array_keys($disks)
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

        note('Stickle can track insert Javascript to track user events and page views.');

        $clientLoadMiddleware = confirm(
            label: 'Do you want to track client-side requests using Javascript?',
            default: false,
            yes: 'Yes',
            no: 'No'
        );

        $settings[] = $this->addSetting('clientLoadMiddleware', $clientLoadMiddleware, 'Client Load Middleware', (bool) $clientLoadMiddleware ? 'Yes' : 'No');

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

        $serverLoadMiddleware = confirm(
            label: 'Would you like to apply these settings?',
            default: false,
            yes: 'Yes',
            no: 'No, I\'ll change settings manually'
        );
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

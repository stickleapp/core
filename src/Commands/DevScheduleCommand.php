<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Carbon\CarbonInterval;
use Illuminate\Console\Command;

final class DevScheduleCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'stickle:dev-schedule';

    /**
     * @var string
     */
    protected $description = 'Run all scheduled commands for development purposes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Running all scheduled commands for development...');
        $this->newLine();

        // Run partition commands if enabled
        if (config('stickle.database.partitionsEnabled')) {
            $this->info('Partitions are enabled, running partition commands...');
            $this->newLine();

            $this->runPartitionCommands();
        } else {
            $this->warn('Partitions are disabled. Skipping partition commands.');
        }

        $this->newLine();

        // Run rollup sessions command
        $this->info('1. Rolling up sessions (3 days back)...');
        $this->call('stickle:rollup-sessions', ['days_ago' => 3]);
        $this->newLine();

        // Run export segments command
        $this->info('2. Exporting segments...');
        $this->call('stickle:export-segments', [
            'namespace' => config('stickle.namespaces.segments'),
            'limit' => 10,
        ]);
        $this->newLine();

        $this->info('✓ All scheduled commands completed!');

        return self::SUCCESS;
    }

    private function runPartitionCommands(): void
    {
        $tablePrefix = config('stickle.database.tablePrefix');
        $schema = config('stickle.database.schema');

        $intervalRequests = config('stickle.database.partitions.requests.interval');
        $extentionRequests = config('stickle.database.partitions.requests.extension');
        $retentionRequests = config('stickle.database.partitions.requests.retention');
        $intervalSessions = config('stickle.database.partitions.sessions.interval');
        $extentionSessions = config('stickle.database.partitions.sessions.extension');
        $retentionSessions = config('stickle.database.partitions.sessions.retention');

        // Create partitions for requests tables
        $this->info('Creating partitions for requests tables...');
        $extensionDate = now()->format('Y-m-d');

        $requestsTables = [
            $tablePrefix.'requests',
            $tablePrefix.'requests_rollup_1min',
            $tablePrefix.'requests_rollup_5min',
            $tablePrefix.'requests_rollup_1hr',
            $tablePrefix.'requests_rollup_1day',
        ];

        foreach ($requestsTables as $table) {
            $this->call('stickle:create-partitions', [
                'existing_table' => $table,
                'schema' => $schema,
                'interval' => $intervalRequests,
                'period_start' => $extensionDate,
                'interval_count' => 3,
            ]);
        }

        // Drop old partitions for requests tables
        $this->info('Dropping old partitions for requests tables...');
        $retentionDate = now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d');

        foreach ($requestsTables as $requestTable) {
            $this->call('stickle:drop-partitions', [
                'existing_table' => $requestTable,
                'schema' => $schema,
                'interval' => $intervalRequests,
                'prior_to_date' => $retentionDate,
            ]);
        }

        // Create partitions for sessions table
        $this->info('Creating partitions for sessions table...');
        $this->call('stickle:create-partitions', [
            'existing_table' => $tablePrefix.'sessions_rollup_1day',
            'schema' => $schema,
            'interval' => $intervalSessions,
            'period_start' => now()->format('Y-m-d'),
            'interval_count' => 3,
        ]);

        // Drop old partitions for sessions table
        $this->info('Dropping old partitions for sessions table...');
        $this->call('stickle:drop-partitions', [
            'existing_table' => $tablePrefix.'sessions_rollup_1day',
            'schema' => $schema,
            'interval' => $intervalSessions,
            'prior_to_date' => now()->sub(CarbonInterval::fromString($retentionSessions))->format('Y-m-d'),
        ]);

        $this->newLine();
    }
}

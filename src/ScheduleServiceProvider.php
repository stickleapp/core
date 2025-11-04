<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Carbon\CarbonInterval;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Override;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('stickle.database.partitionsEnabled')) {
            $this->schedulePartitionJobs();
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {

            // Register the rollup sessions command
            $schedule->command('stickle:rollup-sessions', [
                3, // Go back 3 days by default
            ])->hourly();

            // Register the export segments command
            $schedule->command('stickle:export-segments', [
                config('stickle.namespaces.segments'),
                10, // Limit to 5 segments
            ])->everyFiveMinutes();
        });

    }

    protected function schedulePartitionJobs(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {

            $tablePrefix = config('stickle.database.tablePrefix');
            $schema = config('stickle.database.schema');

            $intervalRequests = config('stickle.database.partitions.requests.interval');
            $extentionRequests = config('stickle.database.partitions.requests.extension');
            $retentionRequests = config('stickle.database.partitions.requests.retention');
            $intervalSessions = config('stickle.database.partitions.sessions.interval');
            $extentionSessions = config('stickle.database.partitions.sessions.extension');
            $retentionSessions = config('stickle.database.partitions.sessions.retention');

            // Requests partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'requests',
                $schema,
                $intervalRequests,
                now()->add(CarbonInterval::fromString($extentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(7, 19, 0);

            // Requests partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'requests_rollup_1min',
                $schema,
                $intervalRequests,
                now()->add(CarbonInterval::fromString($extentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(8, 20, 0);

            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'requests_rollup_5min',
                $schema,
                $intervalRequests,
                now()->add(CarbonInterval::fromString($extentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(9, 21, 0);

            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'requests_rollup_1hr',
                $schema,
                $intervalRequests,
                now()->add(CarbonInterval::fromString($extentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(10, 22, 0);

            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'requests_rollup_1day',
                $schema,
                $intervalRequests,
                now()->add(CarbonInterval::fromString($extentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(11, 23, 0);

            // Requests partition dropping
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'requests_rollup_1min',
                $schema,
                $intervalRequests,
                now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(0, 12, 30);

            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'requests_rollup_5min',
                $schema,
                $intervalRequests,
                now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(1, 13, 30);

            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'requests_rollup_1hr',
                $schema,
                $intervalRequests,
                now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(2, 14, 30);

            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'requests_rollup_1day',
                $schema,
                $intervalRequests,
                now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(3, 15, 30);

            // Sessions partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'sessions_rollup_1day',
                $schema,
                $intervalSessions,
                now()->add(CarbonInterval::fromString($extentionSessions))->format('Y-m-d'),
            ])->twiceDailyAt(4, 16, 30);

            // Sessions partition dropping
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'sessions_rollup_1day',
                $schema,
                $intervalSessions,
                now()->sub(CarbonInterval::fromString($retentionSessions))->format('Y-m-d'),
            ])->twiceDailyAt(5, 17, 30);
        });
    }

    #[Override]
    public function register() {}
}

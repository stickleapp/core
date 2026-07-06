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

            $tablePrefix = config('stickle.database.tablePrefix', 'stc_');
            $schema = config('stickle.database.schema', 'public');

            // Fallback defaults mirror config/stickle.php so an install that upgrades
            // without re-publishing its config still schedules (rather than crashing
            // on a null interval when CarbonInterval::fromString() runs).
            $intervalRequests = config('stickle.database.partitions.requests.interval', 'week');
            $extentionRequests = config('stickle.database.partitions.requests.extension', '1 week');
            $retentionRequests = config('stickle.database.partitions.requests.retention', '1 years');
            $intervalSessions = config('stickle.database.partitions.sessions.interval', 'week');
            $extentionSessions = config('stickle.database.partitions.sessions.extension', '1 week');
            $retentionSessions = config('stickle.database.partitions.sessions.retention', '1 years');
            $intervalModelAttributeAudit = config('stickle.database.partitions.model_attribute_audit.interval', 'week');
            $extentionModelAttributeAudit = config('stickle.database.partitions.model_attribute_audit.extension', '1 week');
            $retentionModelAttributeAudit = config('stickle.database.partitions.model_attribute_audit.retention', '1 years');
            $intervalSegmentStatistics = config('stickle.database.partitions.segment_statistics.interval', 'week');
            $extentionSegmentStatistics = config('stickle.database.partitions.segment_statistics.extension', '1 week');
            $retentionSegmentStatistics = config('stickle.database.partitions.segment_statistics.retention', '1 years');
            $intervalModelRelationshipStatistics = config('stickle.database.partitions.model_relationship_statistics.interval', 'week');
            $extentionModelRelationshipStatistics = config('stickle.database.partitions.model_relationship_statistics.extension', '1 week');
            $retentionModelRelationshipStatistics = config('stickle.database.partitions.model_relationship_statistics.retention', '1 years');

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

            // Model attribute audit partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'model_attribute_audit',
                $schema,
                $intervalModelAttributeAudit,
                now()->add(CarbonInterval::fromString($extentionModelAttributeAudit))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 0);

            // Model attribute audit partition dropping
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'model_attribute_audit',
                $schema,
                $intervalModelAttributeAudit,
                now()->sub(CarbonInterval::fromString($retentionModelAttributeAudit))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 10);

            // Segment statistics partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'segment_statistics',
                $schema,
                $intervalSegmentStatistics,
                now()->add(CarbonInterval::fromString($extentionSegmentStatistics))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 20);

            // Segment statistics partition dropping
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'segment_statistics',
                $schema,
                $intervalSegmentStatistics,
                now()->sub(CarbonInterval::fromString($retentionSegmentStatistics))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 30);

            // Model relationship statistics partition creation
            $schedule->command('stickle:create-partitions', [
                $tablePrefix.'model_relationship_statistics',
                $schema,
                $intervalModelRelationshipStatistics,
                now()->add(CarbonInterval::fromString($extentionModelRelationshipStatistics))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 40);

            // Model relationship statistics partition dropping
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'model_relationship_statistics',
                $schema,
                $intervalModelRelationshipStatistics,
                now()->sub(CarbonInterval::fromString($retentionModelRelationshipStatistics))->format('Y-m-d'),
            ])->twiceDailyAt(6, 18, 50);
        });
    }

    #[Override]
    public function register() {}
}

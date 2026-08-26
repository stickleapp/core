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

            /**
             * Advance the request rollups. Every grain is scheduled, not only
             * the 1day one the filter targets read. The finer grains back the
             * live views, and leaving them unscheduled is not free: each keeps
             * its own last_aggregated_id, so one that has never run is pinned
             * at zero and its first run would sweep the entire history of
             * stc_requests into minute buckets -- needing rollup partitions
             * back to the beginning to survive it. Running them keeps the
             * bookmark current, and a run with nothing new is a no-op.
             *
             * Bucket size does not dictate run frequency: a run aggregates
             * whatever is new into the correct bucket, so a less frequent
             * grain is staler, never wrong.
             */
            $schedule->command('stickle:rollup-requests', ['1min'])
                ->everyMinute()
                ->withoutOverlapping();

            $schedule->command('stickle:rollup-requests', ['5min'])
                ->everyFiveMinutes()
                ->withoutOverlapping();

            $schedule->command('stickle:rollup-requests', ['1hr'])
                ->everyFifteenMinutes()
                ->withoutOverlapping();

            /** Hourly, not daily: this grain backs eventCount and requestCount. */
            $schedule->command('stickle:rollup-requests', ['1day'])
                ->hourly()
                ->withoutOverlapping();

            $schedule->command('stickle:rollup-sessions', [
                3, // Go back 3 days by default
            ])->hourly()->withoutOverlapping();

            /**
             * The four commands below tick every five minutes and decide for
             * themselves what is due: each compares a last-recorded timestamp
             * against its own key in the schedule block of config/stickle.php
             * -- ExportSegmentsCommand:114, RecordModelAttributesCommand:72,
             * RecordSegmentStatisticsCommand:74 and
             * RecordModelRelationshipStatisticsCommand:95.
             *
             * So the cadence here is a floor, not the refresh rate: those
             * config values are the refresh rate, and driving cron from them
             * as well would halve it -- a 360 minute threshold checked every
             * 360 minutes refreshes every 12 hours, not 6.
             */
            $schedule->command('stickle:export-segments', [
                config('stickle.namespaces.segments'),
                10,
            ])->everyFiveMinutes()->withoutOverlapping();

            /**
             * These were registered but never scheduled, so three statistics
             * tables had partitions created and dropped twice daily while
             * nothing ever wrote a row into them.
             */
            $schedule->command('stickle:record-model-attributes', [
                config('stickle.namespaces.models'),
            ])->everyFiveMinutes()->withoutOverlapping();

            $schedule->command('stickle:record-segment-statistics')
                ->everyFiveMinutes()
                ->withoutOverlapping();

            $schedule->command('stickle:record-model-relationship-statistics')
                ->everyFiveMinutes()
                ->withoutOverlapping();

            /**
             * Turns model_segment audit rows into ModelEnteredSegment and
             * ModelExitedSegment events. Unprocessed rows accumulate until it
             * runs, so it runs often.
             */
            $schedule->command('stickle:process-segment-events')
                ->everyFiveMinutes()
                ->withoutOverlapping();
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

            /**
             * stc_requests is the highest-volume table in the package and was
             * the only partitioned one with creation but no retention, so it
             * grew without bound while its own rollups were pruned.
             */
            $schedule->command('stickle:drop-partitions', [
                $tablePrefix.'requests',
                $schema,
                $intervalRequests,
                now()->sub(CarbonInterval::fromString($retentionRequests))->format('Y-m-d'),
            ])->twiceDailyAt(7, 19, 30);

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

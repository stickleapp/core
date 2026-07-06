<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CreatePartitionsCommand extends Command implements Isolatable
{
    /**
     * @var string
     *
     * vendor/bin/testbench stickle:create-partition lc_requests public week '2024-08-01' 2
     * vendor/bin/testbench stickle:create-partition lc_requests_rollup_1min public week '2024-08-01' 2
     * vendor/bin/testbench stickle:create-partition lc_requests_rollup_5min public week '2024-08-01' 2
     * vendor/bin/testbench stickle:create-partition lc_requests_rollup_1hr public week '2024-08-01' 2
     * vendor/bin/testbench stickle:create-partition lc_requests_rollup_1day public week '2024-08-01' 2
     * vendor/bin/testbench stickle:create-partition lc_segment_statistics public week '2024-08-01' 2
     *
     * requests_rollup_1min
     */
    protected $signature = 'stickle:create-partitions
                            {existing_table : The table to be partitioned}
                            {schema : The Postgres Schema of partition}
                            {interval : The interval of the partition}
                            {period_start : The start of the partition period}
                            {interval_count? : The number of partitions to create}';

    /**
     * @var string
     */
    protected $description = 'Created a time-based partitions of the rollup tables (or any table).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        if (! config('stickle.database.partitionsEnabled', true)) {
            $this->info('Partitioning is disabled. Skipping partition creation.');

            return;
        }

        /** @var string $existingTable */
        $existingTable = $this->argument('existing_table');
        /** @var string $schema */
        $schema = $this->argument('schema');
        /** @var string $interval */
        $interval = $this->argument('interval');
        /** @var string $periodStart */
        $periodStart = $this->argument('period_start');
        $intervalCount = (int) $this->argument('interval_count') ?: 1;

        // Snap the requested period start to the beginning of its interval.
        $startDate = $this->startOfInterval(Date::parse($periodStart), $interval);

        // The exclusive end of the requested window: period_start + intervalCount intervals.
        $targetEnd = $startDate->copy()->add($interval, $intervalCount);

        // Always guarantee the current period is covered. When period_start is in
        // the future (as the scheduler passes it: now + extension), back-fill from
        // the current period so "now" — and any gap up to period_start — has a
        // partition. Historical backfills (period_start in the past) are untouched.
        $currentPeriodStart = $this->startOfInterval(Date::now(), $interval);

        $cursor = $startDate->lessThan($currentPeriodStart)
            ? $startDate->copy()
            : $currentPeriodStart->copy();

        $partitionsCreated = 0;

        while ($cursor->lessThan($targetEnd)) {

            $start = $cursor->copy();

            $end = $cursor->copy()->add($interval, 1);

            $partitionName = sprintf($existingTable.'_%s_%s', $interval, preg_replace('/[^A-Za-z0-9 ]/', '', (string) $start->format('YmdHis')));

            $sql = "CREATE TABLE IF NOT EXISTS $schema.$partitionName PARTITION OF $existingTable FOR VALUES FROM ('$start') TO ('$end')";

            DB::unprepared($sql);

            $this->info("Created partition: $partitionName (FROM '$start' TO '$end')");

            $cursor = $end;
            $partitionsCreated++;
        }

        $this->info("Successfully created $partitionsCreated partition(s) for table $existingTable");
    }

    /**
     * Snap a date to the beginning of the given interval.
     */
    private function startOfInterval(Carbon $date, string $interval): Carbon
    {
        switch (strtolower($interval)) {
            case 'day':
                return $date->startOfDay();
            case 'week':
                return $date->startOfWeek(Carbon::MONDAY); // ISO week starts on Monday
            case 'month':
                return $date->startOfMonth();
            case 'year':
                return $date->startOfYear();
            default:
                $this->info("Unknown interval type: $interval. Using date as is.");

                return $date;
        }
    }
}

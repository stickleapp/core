<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
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
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        /**
         * Verify the existing table exists
         */
        $startDate = Date::parse($periodStart);

        // Adjust start date to the beginning of the specified interval
        switch (strtolower($interval)) {
            case 'day':
                $startDate = $startDate->startOfDay();
                break;
            case 'week':
                $startDate = $startDate->startOfWeek(Carbon::MONDAY); // ISO week starts on Monday
                break;
            case 'month':
                $startDate = $startDate->startOfMonth();
                break;
            case 'year':
                $startDate = $startDate->startOfYear();
                break;
            default:
                $this->info("Unknown interval type: $interval. Using date as is.");
        }

        $finished = false;

        $i = 0;
        $partitionsCreated = 0;

        while (! $finished) {

            $start = $startDate->copy()->add($interval, $i);

            $end = $startDate->copy()->add($interval, $i + 1);

            $partitionName = sprintf($existingTable.'_%s_%s', $interval, preg_replace('/[^A-Za-z0-9 ]/', '', (string) $start->format('YmdHis')));

            $sql = "CREATE TABLE IF NOT EXISTS $schema.$partitionName PARTITION OF $existingTable FOR VALUES FROM ('$start') TO ('$end')";

            DB::unprepared($sql);

            $this->info("Created partition: $partitionName (FROM '$start' TO '$end')");

            $i++;
            $partitionsCreated++;

            // Stop after creating the requested number of partitions
            if ($partitionsCreated >= $intervalCount) {
                $finished = true;
            }
        }

        $this->info("Successfully created $partitionsCreated partition(s) for table $existingTable");
    }
}

<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\DB;

final class CreatePartitions extends Command implements Isolatable
{
    /**
     * @var string
     *
     * vendor/bin/testbench cascade:create-partition lc_events public week '2024-08-01' 2
     * vendor/bin/testbench cascade:create-partition lc_events_rollup_1min public week '2024-08-01' 2
     * vendor/bin/testbench cascade:create-partition lc_events_rollup_5min public week '2024-08-01' 2
     * vendor/bin/testbench cascade:create-partition lc_events_rollup_1hr public week '2024-08-01' 2
     * vendor/bin/testbench cascade:create-partition lc_events_rollup_1day public week '2024-08-01' 2
     *
     * events_rollup_1min
     */
    protected $signature = 'cascade:create-partitions
                            {existing_table : The table to be partitioned}
                            {schema : The Postgres Schema of partition}
                            {interval : The interval of the partition}
                            {period_start : The start of the partition period}
                            {interval_count? : The number of partitions to create}';

    /**
     * @var string
     */
    protected $description = 'Created a time-based partitions for the rollup tables (or any table).';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $existingTable = $this->argument('existing_table');
        $schema = $this->argument('schema');
        $interval = $this->argument('interval');
        $periodStart = $this->argument('period_start');
        $intervalCount = (int) $this->argument('interval_count') ?: 0;

        /**
         * Verify the existing table exists
         */
        $startDate = Carbon::parse($periodStart);

        $finished = false;

        $i = 0;
        $j = 0;

        while (! $finished) {

            $start = $startDate->copy()->add($interval, $i);

            $end = $startDate->copy()->add($interval, $i + 1);

            $partitionName = sprintf($existingTable.'_%s_%s', $interval, preg_replace('/[^A-Za-z0-9 ]/', '', $start->format('YmdHis')));

            $sql = "CREATE TABLE IF NOT EXISTS $schema.$partitionName PARTITION OF $existingTable FOR VALUES FROM ('$start') TO ('$end')";

            DB::unprepared($sql);

            $i++;

            // If the period start is in the past
            if ($periodStart < Carbon::now() && $start > Carbon::now()) {
                $j++;
            }

            if ($j > $intervalCount) {
                $finished = true;
            }
        }
    }
}

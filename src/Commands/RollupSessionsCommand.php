<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;

final class RollupSessionsCommand extends Command implements Isolatable
{
    /**
     * @var string
     *
     * vendor/bin/testbench stickle:drop-partition lc_events_rollup_1day public week '2024-10-01'
     */
    protected $signature = 'stickle:rollup-sessions {start_date : First date to consider}';

    /**
     * @var string
     */
    protected $description = 'Total the unique sessions per day based on events and requests tables.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        $startDate = $this->argument('start_date');

        $startDate = Carbon::parse($startDate);

        $sql = <<<sql
INSERT INTO {$this->prefix}sessions_rollup_1day (
    model, 
    object_uid, 
    day, 
    session_count
)
    WITH first_session_events AS (
        SELECT
            model,
            object_uid,
            session_uid,
            MIN(DATE(timestamp)) AS first_day
        FROM
            (SELECT model, object_uid, session_uid, timestamp FROM {$this->prefix}events WHERE timestamp >= '%s'
             UNION ALL
             SELECT model, object_uid, session_uid, timestamp FROM {$this->prefix}requests WHERE offline = FALSE AND timestamp > '%s') AS combined
        GROUP BY
            model,
            object_uid,
            session_uid
    )
    SELECT
        model,
        object_uid,
        first_day AS day,
        COUNT(DISTINCT session_uid) AS session_count
    FROM
        first_session_events
    GROUP BY
        model,
        object_uid,
        first_day
ON CONFLICT (model, object_uid, day) DO UPDATE SET session_count = EXCLUDED.session_count;
sql;

        \DB::statement(sprintf($sql, $startDate->toDateString(), $startDate->toDateString()));
    }
}

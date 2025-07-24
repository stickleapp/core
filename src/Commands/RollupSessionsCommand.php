<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;

final class RollupSessionsCommand extends Command implements Isolatable
{
    /**
     * @var string
     *
     * vendor/bin/testbench stickle:drop-partition lc_events_rollup_1day public week '2024-10-01'
     */
    protected $signature = 'stickle:rollup-sessions {days_ago : How far back to rollup sessions. Defaults to 7 days ago.}';

    /**
     * @var string
     */
    protected $description = 'Total the unique sessions per day based on events and requests tables.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix,
        public readonly AnalyticsRepositoryContract $repository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        $daysAgo = $this->argument('days_ago') ?? 7;

        $startDate = Carbon::now() - subDays($daysAgo);

        $this->repository->rollupSessions($startDate);
    }
}

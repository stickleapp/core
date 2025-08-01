<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

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
     * vendor/bin/testbench stickle:rollup-sessions 3'
     */
    protected $signature = 'stickle:rollup-sessions { days_ago?=7 : How far back to rollup sessions. Defaults to 7 days ago. }';

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

        $daysAgo = (int) $this->argument('days_ago');

        $startDate = now()->subDays($daysAgo);

        $this->repository->rollupSessions($startDate);
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;

final class RollupRequestsCommand extends Command implements Isolatable
{
    /**
     * @var string
     *
     * vendor/bin/testbench stickle:rollup-requests 1day
     */
    protected $signature = 'stickle:rollup-requests
                            {grain=all : Which rollup to advance: 1min, 5min, 1hr, 1day, or all.}';

    /**
     * @var string
     */
    protected $description = 'Aggregate requests into the rollup tables. The 1day grain backs the eventCount and requestCount filters.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        public readonly AnalyticsRepositoryContract $repository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Each grain tracks its own last_aggregated_id against the shared
     * requests sequence, so the grains are independent: running them on
     * different schedules is expected, and a missed run batches into the
     * next one rather than losing rows.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        $grain = (string) $this->argument('grain');

        /**
         * A match rather than in_array() so the literal type survives into
         * rollupRequests(), which interpolates the grain into a function name.
         */
        $grains = match ($grain) {
            'all' => ['1min', '5min', '1hr', '1day'],
            '1min', '5min', '1hr', '1day' => [$grain],
            default => throw new InvalidArgumentException(
                sprintf('Unknown rollup grain [%s]. Expected 1min, 5min, 1hr, 1day, or all.', $grain)
            ),
        };

        foreach ($grains as $each) {
            $this->repository->rollupRequests($each);
        }
    }
}

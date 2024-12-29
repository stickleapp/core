<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class RecordSegmentStatistics extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'cascade:record-segment-statitics {segmentId? : A specific segment to export}
                                                            {attribute? : A specific attribute to export}
                                                            {limit? : The maximum number of segments to export.}';

    /**
     * @var string
     */
    protected $description = 'Store point-in-time values for each segment including value, sum, count, min, max and avg.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[Config('cascade.database.tablePrefix')] public ?string $prefix = null,
    ) {
        $this->prefix = config('cascade.database.tablePrefix');
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        Log::info('RecordSegmentStatistics Command', $this->arguments());

        $segmentId = $this->argument('segmentId');
        $attribute = $this->argument('attribute');
        $limit = $this->argument('limit');

        // we need the segments
        // segments determine the attributes
        // we need the attributes

    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ObjectEnteredSegment;
use StickleApp\Core\Events\ObjectExitedSegment;
use StickleApp\Core\Models\ModelSegmentAudit;

final class ProcessSegmentEventsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:process-segment-events';

    /**
     * @var string
     */
    protected $description = 'Triggger events for new inserts/deletes into the model_segment table.';

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

        /**
         * Retrieve the unprocessed model_segment events
         */
        $builder = ModelSegmentAudit::with('segment')
            ->where(function ($query) {
                $query->whereNull('event_processed_at');
            })
            // ->lazyById(1000, column: 'id')
            ->each(function ($item) {
                if (data_get($item, 'operation') === 'ENTER') {
                    ObjectEnteredSegment::dispatch(
                        $item->object,
                        $item->segment
                    );
                } elseif (data_get($item, 'operation') === 'EXIT') {
                    ObjectExitedSegment::dispatch(
                        $item->object,
                        $item->segment
                    );
                }
                $item->update(['event_processed_at' => now()]);
            });
    }
}

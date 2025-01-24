<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use StickleApp\\Core\Core\Events\ObjectEnteredSegment;
use StickleApp\\Core\Core\Events\ObjectExitedSegment;
use StickleApp\\Core\Core\Models\ObjectSegmentAudit;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;

final class ProcessSegmentEvents extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'STICKLE:process-segment-events';

    /**
     * @var string
     */
    protected $description = 'Triggger events for new inserts/deletes into the object_segment table.';

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
        Log::debug('Processing Segment Events');

        /**
         * Retrieve the unprocessed object_segment events
         */
        $builder = ObjectSegmentAudit::with('segment')
            ->where(function ($query) {
                $query->whereNull('event_processed_at');
            })
            // ->lazyById(1000, column: 'id')
            ->each(function ($item) {
                if ($item->operation === 'ENTER') {
                    ObjectEnteredSegment::dispatch(
                        $item->object,
                        $item->segment
                    );
                } elseif ($item->operation === 'EXIT') {
                    ObjectExitedSegment::dispatch(
                        $item->object,
                        $item->segment
                    );
                }
                $item->update(['event_processed_at' => now()]);
            });
    }
}

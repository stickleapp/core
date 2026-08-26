<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ModelEnteredSegment;
use StickleApp\Core\Events\ModelExitedSegment;
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
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        /**
         * Retrieve the unprocessed model_segment events
         */
        ModelSegmentAudit::with('segment')
            ->where(function (Builder $builder): void {
                $builder->whereNull('event_processed_at');
            })
            // ->lazyById(1000, column: 'id')
            ->each(function ($item): void {
                /**
                 * Resolved once, and both may be null: an audit row outlives the
                 * model and the segment it refers to. Such a row is still marked
                 * processed below -- leaving it unprocessed would retry it on
                 * every run forever.
                 */
                $model = $item->object;
                $segment = $item->segment;

                if ($model !== null && $segment !== null) {
                    if (data_get($item, 'operation') === 'ENTER') {
                        event(new ModelEnteredSegment($model, $segment));
                    } elseif (data_get($item, 'operation') === 'EXIT') {
                        event(new ModelExitedSegment($model, $segment));
                    }
                }

                $item->update(['event_processed_at' => now()]);
            });
    }
}

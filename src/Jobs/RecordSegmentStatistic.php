<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Jobs;

use Dclaysmith\LaravelCascade\Actions\RecordSegmentStatistic as RecordSegmentStatisticAction;
use Dclaysmith\LaravelCascade\Models\Segment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class RecordSegmentStatistic implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?int $segmentId, public ?string $model, public ?string $attribute) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    // public $uniqueFor = 60; // TODO: SET IN CONFIG

    /**
     * Get the unique ID for the job.
     */
    // public function uniqueId(): string
    // {
    //     return $this->segment->id;
    // }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    // public function middleware(): array
    // {
    //     return [new WithoutOverlapping($this->segment->id)];
    // }

    /**
     * Execute the job.
     */
    public function handle(RecordSegmentStatisticAction $recordSegmentStatisticAction): void
    {
        Log::info('RecordSegmentStatistic Job', [
            'segmentId' => $this->segmentId,
            'model' => $this->model,
            'attribute' => $this->attribute,
        ]);

        $recordSegmentStatisticAction(
            segmentId: $this->segmentId,
            model: $this->model,
            attribute: $this->attribute
        );
    }
}

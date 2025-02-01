<?php

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use StickleApp\Core\Actions\ImportSegment as ImportSegmentAction;

class ImportSegment implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $segmentId, public string $exportFilename) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 60; // TODO: SET IN CONFIG

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->segmentId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->segmentId)];
    }

    /**
     * Execute the job.
     */
    public function handle(ImportSegmentAction $importSegment): void
    {
        $importSegment(
            segmentId: $this->segmentId,
            exportFilename: $this->exportFilename
        );
    }
}

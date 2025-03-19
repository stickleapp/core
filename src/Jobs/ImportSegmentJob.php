<?php

declare(strict_types=1);

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Actions\ImportSegmentAction;

class ImportSegmentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $segmentId, public string $exportFilename) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 60; // TODO: SET IN CONFIG

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5(get_class($this).(string) $this->segmentId);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->uniqueId())];
    }

    /**
     * Execute the job.
     */
    public function handle(ImportSegmentAction $importSegment): void
    {
        Log::debug('ImportSegmentJob', ['segment_id' => $this->segmentId, 'exportFilename' => $this->exportFilename]);

        $importSegment(
            segmentId: $this->segmentId,
            exportFilename: $this->exportFilename
        );
    }
}

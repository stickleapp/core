<?php

declare(strict_types=1);

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Actions\ExportSegmentAction;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Models\Segment;

class ExportSegmentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Segment $segment) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 120;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5(get_class($this).(string) $this->segment->id);
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

    public function handle(ExportSegmentAction $exportSegment): void
    {

        Log::debug('ExportSegment Job', ['segment' => $this->segment]);

        $class = config('stickle.namespaces.segments').'\\'.$this->segment->as_class;

        /** @var SegmentContract $segment */
        $segment = new $class;
        $exportFilename = $exportSegment(
            segmentId: $this->segment->id,
            segmentDefinition: $segment
        );

        ImportSegmentJob::dispatch(
            $this->segment->id,
            $exportFilename
        );
    }
}

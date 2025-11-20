<?php

declare(strict_types=1);

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class RecordModelAttributesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Model $stickleEntity) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 120;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5(static::class.$this->stickleEntity::class.$this->stickleEntity->getKey());
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

    public function handle(): void
    {
        Log::info('RecordModelAttributes Job', [
            'modelClass' => $this->stickleEntity::class,
            'modelId' => $this->stickleEntity->getKey(),
        ]);

        /** @phpstan-ignore-next-line */
        $attributes = $this->stickleEntity->stickleTrackedAttributes();
        /** @phpstan-ignore-next-line */
        $this->stickleEntity->trackable_attributes = $this->stickleEntity->only($attributes);
    }
}

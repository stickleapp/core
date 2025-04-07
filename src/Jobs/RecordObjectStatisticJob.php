<?php

declare(strict_types=1);

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Actions\RecordModelRelationshipStatisticAction;

class RecordModelRelationshipStatisticJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $model,
        public string $relationship,
        public string $related,
        public string $attribute
    ) {}

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    // public int $uniqueFor = 120;

    // /**
    //  * Get the unique ID for the job.
    //  */
    // public function uniqueId(): string
    // {
    //     return md5(get_class($this).(string) implode('-', [$this->model, $this->attribute]));
    // }

    // /**
    //  * Get the middleware the job should pass through.
    //  *
    //  * @return array<int, object>
    //  */
    // public function middleware(): array
    // {
    //     return [new WithoutOverlapping($this->uniqueId())];
    // }

    public function handle(RecordModelRelationshipStatisticAction $recordModelRelationshipStatisticAction): void
    {

        Log::info(self::class, [
            'model' => $this->model,
            'relationship' => $this->relationship,
            'related' => $this->related,
            'attribute' => $this->attribute,
        ]);
        if ($this->related == 'Workbench\App\Models\Customer') {
            return;
        }
        $recordModelRelationshipStatisticAction(
            model: $this->model,
            attribute: $this->attribute,
            relationship: $this->relationship,
            related: $this->related
        );
    }
}

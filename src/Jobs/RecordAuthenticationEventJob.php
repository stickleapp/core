<?php

declare(strict_types=1);

namespace StickleApp\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\Request;

/**
 * Write one authentication event row.
 *
 * Everything the row needs arrives on the constructor, captured while the real
 * request was still bound. Nothing here may reach for a Request, a session or
 * the authenticated user: on a queue worker those resolve to a synthetic CLI
 * request, which is how session_uid and ip_address came back null on every
 * driver except sync.
 */
class RecordAuthenticationEventJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $properties
     */
    public function __construct(
        public string $modelClass,
        public string $objectUid,
        public ?string $sessionUid,
        public ?string $ipAddress,
        public Carbon $timestamp,
        public array $properties,
    ) {}

    public function handle(): void
    {
        Log::debug(self::class, [
            'modelClass' => $this->modelClass,
            'objectUid' => $this->objectUid,
        ]);

        Request::query()->create([
            'type' => 'event',
            'model_class' => $this->modelClass,
            'object_uid' => $this->objectUid,
            'session_uid' => $this->sessionUid,
            'ip_address' => $this->ipAddress,
            'timestamp' => $this->timestamp,
            'properties' => $this->properties,
        ]);
    }
}

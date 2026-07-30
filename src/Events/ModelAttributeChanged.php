<?php

declare(strict_types=1);

namespace StickleApp\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelAttributeChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $modelClass,
        public string $objectUid,
        public string $attribute,
        public ?string $from = null,
        public ?string $to = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                config('stickle.broadcasting.channels.firehose')
            ),
            new PrivateChannel(
                sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', strtolower($this->modelClass)),
                    $this->objectUid
                )
            ),
        ];
    }
}

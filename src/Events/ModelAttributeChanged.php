<?php

declare(strict_types=1);

namespace StickleApp\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
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
        public string $model,
        public string $objectUid,
        public string $attribute,
        public ?string $from = null,
        public ?string $to = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(
                config('stickle.broadcasting.channels.firehose')
            ),
            new Channel(
                sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', $this->model),
                    $this->objectUid
                )
            ),
        ];
    }
}

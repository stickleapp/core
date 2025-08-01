<?php

declare(strict_types=1);

namespace StickleApp\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Identify implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<mixed>  $payload
     */
    public function __construct(public array $payload) {}

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
                    str_replace('\\', '-', strtolower(data_get($this->payload, 'model_class'))),
                    data_get($this->payload, 'object_uid')
                )
            ),
        ];
    }
}

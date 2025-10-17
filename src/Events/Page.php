<?php

declare(strict_types=1);

namespace StickleApp\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use StickleApp\Core\Dto\RequestDto;

class Page implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public RequestDto $payload) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(
                config('stickle.broadcasting.channels.firehose')
            ),
            new Channel(
                sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', strtolower($this->payload->model_class)),
                    $this->payload->object_uid
                )
            ),
        ];
    }

    // public function broadcastAs(): string
    // {
    //     return 'page';
    // }
}

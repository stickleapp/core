<?php

namespace StickleApp\Core\Events;

use Illuminate\Container\Attributes\Config;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Track
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<mixed>  $data
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        
        return [
            new PrivateChannel(
                config('stickle.broadcasting.channel.firehose')
            ),
            new PrivateChannel(
                sprintf(config('stickle.broadcasting.channel.object'), 
                    data_get($this->data, 'model'), 
                    data_get($this->data, 'object_uid')
                )
            ),
        ];
    }
}

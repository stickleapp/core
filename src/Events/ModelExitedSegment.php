<?php

declare(strict_types=1);

namespace StickleApp\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use StickleApp\Core\Models\Segment;

class ModelExitedSegment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Model $model, public Segment $segment) {}

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
                    str_replace('\\', '-', strtolower($this->model::class)),
                    $this->model->getKey()
                )
            ),
        ];
    }
}

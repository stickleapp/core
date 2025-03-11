<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

use Illuminate\Support\Facades\Broadcast;

trait StickleBroadcasts
{
    public function handle($event): void
    {
        Broadcast::on($this->channels())
            ->with($this->with())
            ->send($event);

        parent::handle($event);
    }

    abstract public function channels(): array;

    abstract public function with(): string;
}

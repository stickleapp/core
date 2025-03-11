<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

use Illuminate\Support\Facades\Broadcast;

trait StickleBroadcastsPresence
{
    public function handle($event): void
    {
        Broadcast::presence($this->channels())
            ->to($this->to())
            ->with($this->with())
            ->broadcast($event);

        parent::handle($event);
    }

    abstract public function channels(): array;

    abstract public function to(): string;

    abstract public function with(): string;
}

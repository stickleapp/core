<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

trait StickleBroadcastsPrivate
{
    public function handle($event): void
    {
        Broadcast::private($this->channels())
            ->to($this->to())
            ->with($this->with())   
            ->broadcast($event);

        parent::handle($event);
    }

    abstract public function channels(): array;

    abstract public function to(): string;

    abstract public function with(): string;
}

<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use StickleApp\Core\Events\Identify;
use StickleApp\Core\EventServiceProvider;

it('can be instantiated', function (): void {
    $obj = new EventServiceProvider(app());
    expect($obj)->toBeInstanceOf(EventServiceProvider::class);
});

it('registers events correctly', function (): void {
    $events = app(Dispatcher::class);
    $listeners = $events->getListeners(Identify::class);
    expect($listeners)
        ->toBeArray()
        ->toHaveCount(1);
});

<?php

declare(strict_types=1);

use StickleApp\Core\Events\Identify;
use StickleApp\Core\EventServiceProvider;

it('can be instantiated', function () {
    $obj = new EventServiceProvider(app());
    expect($obj)->toBeInstanceOf(EventServiceProvider::class);
});

it('registers events correctly', function () {
    $events = app('events');
    $listeners = $events->getListeners(Identify::class);
    expect($listeners)
        ->toBeArray()
        ->toHaveCount(1);
});

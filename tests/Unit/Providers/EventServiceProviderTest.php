<?php

use StickleApp\Core\Events\Identify;
use StickleApp\Core\Providers\EventServiceProvider;

it('can be instantiated', function () {
    $obj = new EventServiceProvider(app());
    expect($obj)->toBeInstanceOf(EventServiceProvider::class);
});

it('registers events correctly', function () {
    // $obj = new EventServiceProvider(app());
    // $obj->register();

    // Assuming you have some events and listeners defined in your EventServiceProvider
    $events = app('events');
    $listeners = $events->getListeners(Identify::class);
    expect($listeners)
        ->toBeArray()
        ->toHaveCount(2); // this + a closure based
});

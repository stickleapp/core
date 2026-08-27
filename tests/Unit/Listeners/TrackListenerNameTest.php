<?php

declare(strict_types=1);

use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Dto\ModelDto;
use StickleApp\Core\Dto\RequestDto;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Listeners\TrackListener;

function trackNamed(string $name): Track
{
    return new Track(new RequestDto(
        type: 'track',
        model_class: 'User',
        object_uid: '1',
        session_uid: null,
        timestamp: now(),
        model: new ModelDto('User', '1', 'A User', [], '/user/1'),
        ip_address: null,
        properties: ['name' => $name],
        location_data: null,
    ));
}

/**
 * The colon form is what the package's own examples emit, and Str::studly only
 * splits on - and _, so `document:signed` produced `Document:signed` --
 * not a class name, so class_exists() was false and the listener never ran.
 */
test('every documented event name spelling resolves to the same listener', function (string $name): void {
    $listener = new TrackListener(Mockery::mock(AnalyticsRepositoryContract::class));

    expect($listener->listenerName(trackNamed($name)))
        ->toBe('Workbench\App\Listeners\IDidAThingListener');
})->with([
    'i:did:a:thing',
    'i_did_a_thing',
    'i-did-a-thing',
    'i.did.a.thing',
    'IDidAThing',
]);

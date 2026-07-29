<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/**
 * These tests call the closures registered in routes/channels.php directly
 * via Broadcast::driver()->getChannels(), bypassing HTTP entirely. That
 * proves the closures' own logic is correct — it does NOT prove anything in
 * the application ever invokes them.
 *
 * Nothing does, today: every event in src/Events/ broadcasts on a public
 * `new Channel(...)`, and Laravel only routes a subscription through
 * /broadcasting/auth (and therefore through these closures) for channels
 * prefixed `private-`/`presence-`. So this suite is green while the
 * realtime stream is unauthenticated end-to-end — that gap is exactly why
 * it survived six task reviews on this branch. See the "NOT CURRENTLY
 * CONSULTED" note atop routes/channels.php.
 */
function stickleChannelCallback(string $configKey): callable
{
    $channels = Broadcast::driver()->getChannels();

    $pattern = config($configKey);

    expect($channels)->toHaveKey($pattern);

    return $channels[$pattern];
}

it('denies the firehose channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeFalse();
});

it('denies the firehose channel when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeFalse();
});

it('allows the firehose channel when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeTrue();
});

it('denies the object channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.object');

    expect($callback(null, 'user', '1'))->toBeFalse();
});

it('denies the class channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.class');

    expect($callback(null, 'user'))->toBeFalse();
});

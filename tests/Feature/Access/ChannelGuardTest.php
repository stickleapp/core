<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

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

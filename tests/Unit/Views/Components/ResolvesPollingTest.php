<?php

declare(strict_types=1);

use StickleApp\Core\Views\Components\Ui\Maps\Live;
use StickleApp\Core\Views\Components\Ui\Timelines\Events;
use StickleApp\Core\Views\Components\Ui\Timelines\Sessions;

it('falls back to the default interval when the config is not published', function (): void {

    config()->offsetUnset('stickle.broadcasting.polling');

    $live = resolve(Live::class);

    expect($live->pollInterval)->toBe(Live::DEFAULT_POLL_INTERVAL);
    expect($live->pollInterval)->toBe(15);
    expect($live->pollingEnabled)->toBeTrue();
});

it('takes the interval from config', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 30);

    expect(resolve(Live::class)->pollInterval)->toBe(30);
});

it('coerces an interval arriving from the environment as a string', function (): void {

    config()->set('stickle.broadcasting.polling.interval', '45');

    expect(resolve(Live::class)->pollInterval)->toBe(45);
});

it('ignores a non-positive interval', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 0);

    expect(resolve(Live::class)->pollInterval)->toBe(15);
});

it('lets an attribute override the configured interval', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 30);

    $live = Live::resolve(['pollInterval' => 5]);

    expect($live->pollInterval)->toBe(5);
});

it('disables polling from config', function (): void {

    config()->set('stickle.broadcasting.polling.enabled', false);

    expect(resolve(Live::class)->pollingEnabled)->toBeFalse();
});

it('coerces a disabled flag arriving from the environment as a string', function (): void {

    config()->set('stickle.broadcasting.polling.enabled', 'false');

    expect(resolve(Live::class)->pollingEnabled)->toBeFalse();
});

it('lets an attribute override polling being enabled', function (): void {

    config()->set('stickle.broadcasting.polling.enabled', true);

    $live = Live::resolve(['pollingEnabled' => false]);

    expect($live->pollingEnabled)->toBeFalse();
});

it('resolves polling on every component that streams live updates', function (string $component): void {

    config()->set('stickle.broadcasting.polling.interval', 20);

    $instance = $component::resolve([
        'channel' => 'stickle.firehose',
        'requestsEndpoint' => '/stickle/api/requests',
    ]);

    expect($instance->pollInterval)->toBe(20);
    expect($instance->pollingEnabled)->toBeTrue();
})->with([
    Live::class,
    Sessions::class,
    Events::class,
]);

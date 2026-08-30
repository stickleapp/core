<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the resolved interval into the events timeline', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 25);

    $html = Blade::render(
        '<x-stickle::ui.timelines.events channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    expect($html)->toContain('intervalSeconds: 25');
    expect($html)->toContain('pollingEnabled: true');
    expect($html)->toContain('endpoint: "/stickle/api/requests"');
});

it('renders the resolved interval into the sessions timeline', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 25);

    $html = Blade::render(
        '<x-stickle::ui.timelines.sessions channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    expect($html)->toContain('intervalSeconds: 25');
});

it('renders the resolved interval into the live map', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 25);

    $html = Blade::render(
        '<x-stickle::ui.maps.live channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    expect($html)->toContain('intervalSeconds: 25');
});

it('renders polling as disabled when the config turns it off', function (): void {

    config()->set('stickle.broadcasting.polling.enabled', false);

    $html = Blade::render(
        '<x-stickle::ui.timelines.events channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    expect($html)->toContain('pollingEnabled: false');
});

it('renders a per-usage interval override', function (): void {

    config()->set('stickle.broadcasting.polling.interval', 25);

    $html = Blade::render(
        '<x-stickle::ui.timelines.events channel="stickle.firehose" requests-endpoint="/stickle/api/requests" :poll-interval="60" />'
    );

    expect($html)->toContain('intervalSeconds: 60');
});

it('times events off the timestamp both transports actually send', function (): void {

    $html = Blade::render(
        '<x-stickle::ui.timelines.events channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    // Neither a broadcast payload nor a polled record carries created_at, so
    // reading it rendered every event, however old, as "just now".
    expect($html)->not->toContain('created_at');
    expect($html)->toContain('stickleTimeAgo(event?.data?.timestamp)');
});

it('backfills the events timeline from the read API before the stream starts', function (): void {

    $html = Blade::render(
        '<x-stickle::ui.timelines.events channel="stickle.firehose" requests-endpoint="/stickle/api/requests" />'
    );

    // The stream is live-only while the websocket is healthy, so history has
    // to come from one bounded read of the endpoint, seeded into the stream
    // so neither transport replays it.
    expect($html)->toContain('per_page');
    expect($html)->toContain('stream.seed(');
});

it('leaves the endpoint empty when the events timeline is given none', function (): void {

    $html = Blade::render('<x-stickle::ui.timelines.events channel="stickle.firehose" />');

    expect($html)->toContain('endpoint: ""');
});

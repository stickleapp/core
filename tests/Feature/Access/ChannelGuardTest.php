<?php

declare(strict_types=1);

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use StickleApp\Core\Dto\ModelDto;
use StickleApp\Core\Dto\RequestDto;
use StickleApp\Core\Events\ModelAttributeChanged;
use StickleApp\Core\Events\ModelEnteredSegment;
use StickleApp\Core\Events\ModelExitedSegment;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Models\Segment;
use Workbench\App\Models\User;

/**
 * These tests drive channel authorization the way a browser does: over HTTP,
 * to /broadcasting/auth, using the channel names the events in src/Events/
 * actually broadcast on.
 *
 * That matters. The previous version of this file pulled the closures out of
 * Broadcast::driver()->getChannels() and called them directly, which proved
 * their logic but never that anything reaches them -- and nothing did, because
 * the events broadcast on public channels and Laravel only routes `private-`
 * and `presence-` subscriptions through /broadcasting/auth. The suite was
 * green while the stream was open to anyone with the app key. Going through
 * the endpoint with a name taken from an event means a return to public
 * channels shows up here as an unauthenticated request being *allowed*.
 *
 * Two pieces of setup below stand in for the host application, because they
 * are the host application's job in a real install:
 *
 *  - Choosing a broadcaster. The suite's default is `log`, whose auth() is an
 *    empty method, so it cannot tell allow from deny (`null` is the same).
 *    `redis` is used here because it is framework-native -- no third-party
 *    SDK -- and its auth() is the same guarded-channel + Gate path Pusher's
 *    and Reverb's take. Authorizing a channel never touches the connection,
 *    so no Redis server is required.
 *
 *  - Calling Broadcast::routes(). Stickle deliberately does not register the
 *    /broadcasting/auth endpoint on an application's behalf; see README.md.
 *
 * routes/channels.php is re-run after switching the default connection
 * because Broadcast::channel() registers on whichever broadcaster is default
 * at the time, and each broadcaster keeps its own channel registry. The
 * package's own file is what runs, so the registrations under test are the
 * shipped ones.
 */
beforeEach(function (): void {
    config()->set('broadcasting.connections.redis', [
        'driver' => 'redis',
        'connection' => 'default',
    ]);

    config()->set('broadcasting.default', 'redis');

    require dirname(__DIR__, 3).'/routes/channels.php';

    Broadcast::routes();
});

/**
 * @param  class-string  $event
 */
function stickleBroadcastEvent(string $event): object
{
    $dto = new RequestDto(
        type: 'page',
        model_class: User::class,
        object_uid: '1',
        session_uid: 'session-uid',
        timestamp: Date::parse('2026-07-29 12:00:00'),
        model: new ModelDto(
            model_class: User::class,
            object_uid: '1',
            label: 'Ada',
            raw: [],
            url: '/stickle/user/1',
        ),
        ip_address: '127.0.0.1',
        properties: null,
        location_data: null,
    );

    /** Unsaved: broadcastOn() only reads the class and the key. */
    $user = (new User)->forceFill(['id' => 1]);

    return match ($event) {
        Track::class => new Track($dto),
        Page::class => new Page($dto),
        ModelAttributeChanged::class => new ModelAttributeChanged(User::class, '1', 'email'),
        ModelEnteredSegment::class => new ModelEnteredSegment($user, new Segment),
        ModelExitedSegment::class => new ModelExitedSegment($user, new Segment),
        default => throw new InvalidArgumentException($event),
    };
}

/**
 * Any authenticated user: the Gate decides, and these tests define what it
 * decides. Unsaved, with the attributes RequestLogger reads off the actor.
 */
function stickleSubscriber(): User
{
    return (new User)->forceFill(['id' => 1, 'name' => 'Ada Lovelace']);
}

/**
 * The name that reaches the wire -- and therefore /broadcasting/auth -- for
 * one of an event's channels. PrivateChannel prefixes `private-`; a public
 * Channel does not, which is the whole difference this file exists to catch.
 */
function stickleWireChannel(string $event, int $index = 0): string
{
    return (string) stickleBroadcastEvent($event)->broadcastOn()[$index];
}

it('broadcasts only on private channels', function (string $event): void {

    $channels = stickleBroadcastEvent($event)->broadcastOn();

    expect($channels)->not->toBeEmpty();

    foreach ($channels as $channel) {
        expect($channel)->toBeInstanceOf(PrivateChannel::class)
            ->and($channel->name)->toStartWith('private-');
    }
})->with([
    Track::class,
    Page::class,
    ModelAttributeChanged::class,
    ModelEnteredSegment::class,
    ModelExitedSegment::class,
]);

it('rejects an unauthenticated subscription', function (int $index): void {

    /**
     * The gate allows (TestCase defines it for the whole suite), so a denial
     * here can only come from the channel being guarded at all. Make the
     * channels public again and Laravel stops guarding them: this request is
     * authorized instead, and this test fails.
     */
    $this->post('/broadcasting/auth', [
        'channel_name' => stickleWireChannel(Track::class, $index),
        'socket_id' => '1234.5678',
    ])->assertForbidden();
})->with([
    'firehose' => 0,
    'object' => 1,
]);

it('authorizes a subscription for a user the gate allows', function (string $channel): void {

    $response = $this->actingAs(stickleSubscriber())->post('/broadcasting/auth', [
        'channel_name' => $channel,
        'socket_id' => '1234.5678',
    ]);

    $response->assertOk();

    expect($response->getContent())->toBe('true');
})->with([
    /**
     * Taken from the events themselves, so the authorizer patterns in
     * routes/channels.php have to match the names the events broadcast on.
     */
    'firehose' => fn (): string => stickleWireChannel(Track::class),
    'object' => fn (): string => stickleWireChannel(Track::class, 1),
    /**
     * The class channel has no event of its own; resources/views/pages/
     * models.blade.php subscribes the models timeline to it.
     */
    'class' => fn (): string => 'private-'.sprintf(
        (string) config('stickle.broadcasting.channels.class'),
        'user',
    ),
    /**
     * The object channel as the UI builds it (class basename rather than the
     * full namespace the events use), so both spellings stay authorizable.
     */
    'object as the ui names it' => fn (): string => 'private-'.sprintf(
        (string) config('stickle.broadcasting.channels.object'),
        'user',
        '1',
    ),
]);

it('rejects a subscription when the application defines no gate', function (): void {

    withoutStickleGate();

    $this->actingAs(stickleSubscriber())->post('/broadcasting/auth', [
        'channel_name' => stickleWireChannel(Track::class),
        'socket_id' => '1234.5678',
    ])->assertForbidden();
});

it('rejects a subscription when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->actingAs(stickleSubscriber())->post('/broadcasting/auth', [
        'channel_name' => stickleWireChannel(Track::class),
        'socket_id' => '1234.5678',
    ])->assertForbidden();
});

it('passes only the user to the gate', function (int $index): void {

    $received = null;

    Gate::define('viewStickle', function ($user = null) use (&$received): bool {
        $received = func_get_args();

        return true;
    });

    $this->actingAs(stickleSubscriber())->post('/broadcasting/auth', [
        'channel_name' => stickleWireChannel(Track::class, $index),
        'socket_id' => '1234.5678',
    ])->assertOk();

    /**
     * The channel pattern carries the model and id, but they are not
     * forwarded: a viewStickle defined for the HTTP routes, which never
     * receive them, must not error out here.
     */
    expect($received)->toHaveCount(1);
})->with([
    'firehose' => 0,
    'object' => 1,
]);

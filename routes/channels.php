<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/**
 * NOT CURRENTLY CONSULTED. These authorizers are correct and will matter
 * once the channels below are made private, but today nothing calls them.
 *
 * Every event in src/Events/ broadcasts on `new Channel(...)` — a public
 * channel — not `PrivateChannel`. Laravel (and pusher-js) only route a
 * subscription through /broadcasting/auth, and therefore through the
 * closures registered here, for channel names prefixed `private-` or
 * `presence-`. A public channel name never triggers that request, so this
 * file has no effect on who can subscribe.
 *
 * The practical result: Stickle's realtime broadcast stream is presently
 * unauthenticated. Anyone holding the application's Reverb/Pusher app key
 * can subscribe to these channel names directly and receive every tracked
 * event, unrelated to whether they pass viewStickle. See UPGRADE.md and
 * README.md for the operational implications.
 *
 * This file becomes effective only once the events in src/Events/ broadcast
 * on PrivateChannel/PresenceChannel and the JS client (currently
 * Echo.channel(), see default-layout.blade.php) subscribes with
 * Echo.private()/Echo.join() instead. That is separate work, tracked but
 * not part of this change.
 *
 * Once wired up, this is the same viewStickle ability that guards the HTTP
 * routes, so the two transports cannot drift apart. Laravel denies an
 * ability that was never defined, so an application that has not defined it
 * rejects subscriptions.
 *
 * viewStickle receives only the user, over both transports. Route
 * parameters are still accepted by these closures (Laravel requires the
 * signature to match the channel pattern) but are not forwarded to the
 * Gate: passing them would let a `viewStickle` defined for one transport
 * error out on the other, for no real gain -- the same data is reachable
 * through the read API, which never receives them either.
 */
Broadcast::channel(config('stickle.broadcasting.channels.firehose'), fn ($user): bool => Gate::forUser($user)->allows('viewStickle'));

Broadcast::channel(config('stickle.broadcasting.channels.object'), fn ($user, $model, $id): bool => Gate::forUser($user)->allows('viewStickle'));

Broadcast::channel(config('stickle.broadcasting.channels.class'), fn ($user, $model): bool => Gate::forUser($user)->allows('viewStickle'));

<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/**
 * Who may subscribe to Stickle's realtime stream.
 *
 * Every event in src/Events/ broadcasts on a PrivateChannel, so a client
 * subscribing to one of these channels is routed through /broadcasting/auth
 * first and lands in the closures below. This is the same viewStickle
 * ability that guards the HTTP routes, so the two transports cannot drift
 * apart. Laravel denies an ability that was never defined, so an application
 * that has not defined it rejects subscriptions.
 *
 * These closures only run if the application registers the endpoint they
 * live behind. Stickle does not call Broadcast::routes() on an application's
 * behalf -- that is the host application's decision -- and without it there
 * is nowhere for a subscription to be authorized. See README.md and
 * docs/guide/configuration.md.
 *
 * The channel names come from config, where they are sprintf() formats
 * shared with the event classes and the Blade views that subscribe. They are
 * rewritten here into Laravel's `{parameter}` pattern syntax so that one
 * pattern matches every name a format can produce: `stickle.object.%s.%s`
 * has to be registered as `stickle.object.{model}.{id}`, or it matches
 * nothing and every subscription to an object channel is denied.
 *
 * Laravel prefixes `private-` on the wire; the patterns registered here stay
 * un-prefixed, because the broadcaster strips that prefix before matching.
 *
 * viewStickle receives only the user, over both transports. Route parameters
 * are still accepted by these closures (Laravel requires the signature to
 * match the channel pattern) but are not forwarded to the Gate: passing them
 * would let a `viewStickle` defined for one transport error out on the
 * other, for no real gain -- the same data is reachable through the read
 * API, which never receives them either.
 */
Broadcast::channel(config('stickle.broadcasting.channels.firehose'), fn ($user): bool => Gate::forUser($user)->allows('viewStickle'));

Broadcast::channel(sprintf((string) config('stickle.broadcasting.channels.object'), '{model}', '{id}'), fn ($user, $model, $id): bool => Gate::forUser($user)->allows('viewStickle'));

Broadcast::channel(sprintf((string) config('stickle.broadcasting.channels.class'), '{model}'), fn ($user, $model): bool => Gate::forUser($user)->allows('viewStickle'));

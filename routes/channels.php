<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/**
 * The same viewStickle ability that guards the HTTP routes, so the two
 * transports cannot drift apart. Laravel denies an ability that was never
 * defined, so an application that has not defined it rejects subscriptions.
 *
 * Route parameters are forwarded as the Gate's context, which lets an
 * application scope per record without being required to: PHP ignores extra
 * arguments passed to a closure, so fn ($user) => $user->is_admin still works.
 */
Broadcast::channel(config('stickle.broadcasting.channels.firehose'), fn ($user): bool => Gate::forUser($user)->allows('viewStickle'));

Broadcast::channel(config('stickle.broadcasting.channels.object'), fn ($user, $model, $id): bool => Gate::forUser($user)->allows('viewStickle', [$model, $id]));

Broadcast::channel(config('stickle.broadcasting.channels.class'), fn ($user, $model): bool => Gate::forUser($user)->allows('viewStickle', [$model]));

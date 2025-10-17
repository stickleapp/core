<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(config('stickle.broadcasting.channels.firehose'), function ($user): true {
    return true; // ($user->is_admin) ? $user : false;
});

Broadcast::channel(config('stickle.broadcasting.channels.object'), function ($user, $model, $id): true {
    return true; // ($user->is_admin) ? $user : false;
});

Broadcast::channel(config('stickle.broadcasting.channels.class'), function ($user, $model): true {
    return true; // ($user->is_admin) ? $user : false;
});

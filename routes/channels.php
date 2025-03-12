<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(config('stickle.broadcasting.channels.firehose'), function ($user) {
    return true; // ($user->is_admin) ? $user : false;
});

Broadcast::channel(config('stickle.broadcasting.channels.object'), function ($user, $model, $id) {
    return true; // ($user->is_admin) ? $user : false;
});

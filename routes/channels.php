<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('Admin', function ($user) {
    return ($user->is_admin) ? $user : false;
});

Broadcast::channel('Private.{id}', function ($user, $id) {
    return ((int) $user->id === (int) $id) ? true : false;
});

Broadcast::channel('Presence.{id}', function ($user, $id) {
    return ((int) $user->id === (int) $id) ? $model : false;
});

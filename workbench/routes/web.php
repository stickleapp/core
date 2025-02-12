<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use StickleApp\Core\Events\Track;
use Workbench\App\Middleware\AuthInline;
use Workbench\App\Models\User;

Route::get('{any}', function (Request $request) {
    // We just want the web middleware to process these
    return Session::token();
})->middleware(AuthInline::class)->where('any', '.*'); // Matches any route

Route::post('/users/{user}/{event}', function (Request $request, User $user, string $event) {
    \Log::info('Post Route');

    $dt = now();
    $data = [
        'model' => User::class,
        'object_uid' => $user->id,
        'session_uid' => $request->session()->getId(),
        'timestamp' => $dt,
        'event' => $event,
        'properties' => [],
        'url' => $request->fullUrl(),
        'path' => $request->getPathInfo(),
        'host' => $request->getHost(),
        'search' => $request->getQueryString(),
        'utm_source' => $request->query('utm_source'),
        'utm_medium' => $request->query('utm_medium'),
        'utm_campaign' => $request->query('utm_campaign'),
        'utm_content' => $request->query('utm_content'),
        'created_at' => $dt,
        'updated_at' => $dt,
    ];
    Track::dispatch($data);

    return 'OK';
})->middleware(AuthInline::class); // Matches any route

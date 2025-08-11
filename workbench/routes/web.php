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
        'activity_type' => 'event',
        'model_class' => class_basename(User::class),
        'object_uid' => (string) $user->id,
        'session_uid' => $request->session()->getId(),
        'timestamp' => $dt,
        'ip_address' => $request->ip(),
        'model' => $user,
        'name' => $event,
        'properties' => [
            'url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
            'host' => $request->getHost(),
            'search' => $request->getQueryString(),
            'search' => $request->getQueryString(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
        ],
        'created_at' => $dt,
        'updated_at' => $dt,
    ];
    Track::dispatch($data);

    return 'OK';
})->middleware(AuthInline::class); // Matches any route

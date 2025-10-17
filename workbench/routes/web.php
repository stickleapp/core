<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use StickleApp\Core\Dto\RequestDto;
use StickleApp\Core\Events\Track;
use Workbench\App\Middleware\AuthInline;
use Workbench\App\Models\User;

Route::get('{any}', fn (Request $request) =>
    // We just want the web middleware to process these
    Session::token())->middleware(AuthInline::class)->where('any', '.*'); // Matches any route

Route::post('/users/{user}/{event}', function (Request $request, User $user, string $event): string {
    Log::info('Post Route');

    $dt = now();
    $data = [
        'type' => 'event',
        'model_class' => class_basename(User::class),
        'object_uid' => (string) $user->id,
        'session_uid' => $request->session()->getId(),
        'timestamp' => $dt,
        'ip_address' => DB::table('stc_location_data')->inRandomOrder()->value('ip_address') ?? $request->ip(),
        'model' => [
            'model_class' => $user::class,
            'object_uid' => (string) $user->id,
            'label' => $user->stickleLabel(),
            'raw' => $user->toArray(),
            'url' => $user->stickleUrl(),
        ],
        'location_data' => null,
        'properties' => [
            'name' => $event,
            'url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
            'host' => $request->getHost(),
            'search' => $request->getQueryString(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
        ],
    ];
    event(new Track(RequestDto::fromArray($data)));

    return 'OK';
})->middleware(AuthInline::class); // Matches any route

<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Listeners\AuthenticatableEventListener;
use StickleApp\Core\Models\Request as StickleRequest;
use Workbench\App\Models\User;

function listenerForRequest(Request $request): AuthenticatableEventListener
{
    return new AuthenticatableEventListener(
        $request,
        Mockery::mock(AnalyticsRepositoryContract::class)
    );
}

/**
 * Session\Store regenerates any id that is not 40 alphanumeric characters, so
 * a readable placeholder would silently not be the id under test.
 */
function requestWithSession(string $id): Request
{
    $store = new Store('stickle_session', new ArraySessionHandler(120), $id);

    $request = Request::create('/dashboard');
    $request->setLaravelSession($store);

    return $request;
}

/**
 * session_uid identifies a session. It was being handed `new DateTime`, so
 * every authentication event row carried a timestamp in the column that joins
 * events to sessions.
 */
test('an authentication event records the session it happened in', function (): void {
    $user = User::factory()->create();

    listenerForRequest(requestWithSession(str_repeat('a1b2', 10)))
        ->onEvent(new Login('web', $user, false));

    $request = StickleRequest::query()->where('type', 'event')->sole();

    expect($request->session_uid)->toBe(str_repeat('a1b2', 10));
});

test('an authentication event outside a session records no session', function (): void {
    $user = User::factory()->create();

    listenerForRequest(Request::create('/dashboard'))
        ->onEvent(new Login('web', $user, false));

    $request = StickleRequest::query()->where('type', 'event')->sole();

    expect($request->session_uid)->toBeNull();
});

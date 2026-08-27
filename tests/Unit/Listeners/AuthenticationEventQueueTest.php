<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Queue;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Jobs\RecordAuthenticationEventJob;
use StickleApp\Core\Listeners\AuthenticatableEventListener;
use StickleApp\Core\Models\Request as StickleRequest;
use Workbench\App\Models\User;

const CAPTURED_SESSION = 'c0ffee01c0ffee02c0ffee03c0ffee04c0ffee05';
const CAPTURED_IP = '203.0.113.9';

function requestOnTheWebThread(): Request
{
    $request = Request::create('/login', 'POST', server: ['REMOTE_ADDR' => CAPTURED_IP]);
    $request->setLaravelSession(
        new Store('stickle_session', new ArraySessionHandler(120), CAPTURED_SESSION)
    );

    return $request;
}

/**
 * The listener used to be ShouldQueue, so onEvent() ran on a worker and the
 * Request it injected was resolved from the worker's container -- a synthetic
 * CLI request with no session and no client IP. Both columns came back null on
 * every queue driver except sync.
 *
 * The capture has to happen while the real request is still bound, and only the
 * write belongs on the queue.
 */
test('the session and ip are captured on the web thread and carried by the job', function (): void {
    $user = User::factory()->create();

    $request = requestOnTheWebThread();
    app()->instance('request', $request);
    app()->instance(Request::class, $request);

    Queue::fake();

    $dispatcher = new Dispatcher(app());
    (new AuthenticatableEventListener($request, Mockery::mock(AnalyticsRepositoryContract::class)))
        ->subscribe($dispatcher);

    $dispatcher->dispatch(new Login('web', $user, false));

    Queue::assertPushed(
        RecordAuthenticationEventJob::class,
        fn (RecordAuthenticationEventJob $recordAuthenticationEventJob): bool => $recordAuthenticationEventJob->sessionUid === CAPTURED_SESSION
            && $recordAuthenticationEventJob->ipAddress === CAPTURED_IP
    );
});

/**
 * The worker must never need a request. This hands the job a bare one to prove
 * it is not consulted.
 */
test('the job writes the captured values without consulting a request', function (): void {
    $user = User::factory()->create();

    app()->instance('request', Request::create('/'));

    (new RecordAuthenticationEventJob(
        modelClass: 'User',
        objectUid: (string) $user->getKey(),
        sessionUid: CAPTURED_SESSION,
        ipAddress: CAPTURED_IP,
        timestamp: now(),
        properties: ['name' => Login::class],
    ))->handle();

    $request = StickleRequest::query()->where('type', 'event')->sole();

    expect($request->session_uid)->toBe(CAPTURED_SESSION)
        ->and($request->ip_address)->toBe(CAPTURED_IP)
        ->and($request->model_class)->toBe('User')
        ->and($request->object_uid)->toBe((string) $user->getKey());
});

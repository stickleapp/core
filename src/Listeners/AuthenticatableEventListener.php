<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Jobs\RecordAuthenticationEventJob;
use StickleApp\Core\Support\ClassUtils;

/**
 * Deliberately NOT ShouldQueue.
 *
 * A queued listener is constructed by the worker's container, so the Request it
 * injects is a synthetic CLI request -- no session, no client IP. This has to
 * run while the real request is still bound, which is the only moment those
 * values exist. The database write is what goes on the queue, via
 * RecordAuthenticationEventJob, which carries the captured values rather than
 * re-reading them.
 *
 * PageListener and TrackListener stay queued because their request state was
 * already captured upstream by RequestLogger and rides in on a RequestDto.
 * Laravel's auth events carry no such payload, so the capture happens here.
 */
class AuthenticatableEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct(public Request $request, public AnalyticsRepositoryContract $repository) {}

    public function onEvent(mixed $event): void
    {
        Log::debug('AuthenticatableEventListener->onEvent', [$event]);

        if (! $event->user) {
            return;
        }

        $properties = [
            'name' => $event::class ?: 'UnknownEvent',
        ];

        /**
         * The request is the only source for the session and the IP, and this
         * is the only moment they are available. Read them here and hand them
         * to the job as plain values.
         *
         * session_uid once held `new DateTime` -- a timestamp in the column
         * that joins an event to its session. ip_address and timestamp were
         * read off $event->payload, which no Illuminate\Auth\Events class
         * defines, so the read raised Undefined property and no row was written
         * at all.
         */
        dispatch(
            new RecordAuthenticationEventJob(
                modelClass: ClassUtils::storeModelClass($event->user),
                objectUid: (string) $event->user->getKey(),
                sessionUid: $this->request->hasSession() ? $this->request->session()->getId() : null,
                ipAddress: $this->request->header('X-Forwarded-For') ?: $this->request->ip(),
                timestamp: Date::now(),
                properties: $properties
            )
        );
    }

    /**
     * Every name STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED accepts.
     *
     * Public because it is the authority the config is validated against, and
     * because an installer or a test has no other way to know the valid set.
     *
     * @var array<string, class-string>
     */
    public const array EVENT_CLASSES = [
        'Authenticated' => Authenticated::class,
        'CurrentDeviceLogout' => CurrentDeviceLogout::class,
        'Login' => Login::class,
        'Logout' => Logout::class,
        'OtherDeviceLogout' => OtherDeviceLogout::class,
        'PasswordReset' => PasswordReset::class,
        'Registered' => Registered::class,
        'Validated' => Validated::class,
        'Verified' => Verified::class,
    ];

    /**
     * Register the listeners for the subscriber.
     *
     * An entry that names no event is fatal rather than ignored. The list is
     * parsed by splitting an env var on commas, so a boolean becomes ['1'] --
     * non-empty, which is enough for EventServiceProvider to register this
     * subscriber, but matching nothing, so not one event row is ever written.
     * Failing here is the only thing that distinguishes that from tracking
     * being switched off on purpose.
     */
    public function subscribe(Dispatcher $dispatcher): void
    {
        /** @var array<int, string> $trackedEvents */
        $trackedEvents = config('stickle.tracking.server.authenticationEventsTracked', []);

        $this->validateTrackedEvents($trackedEvents);

        foreach (array_intersect_key(self::EVENT_CLASSES, array_flip($trackedEvents)) as $eventClass) {
            $dispatcher->listen($eventClass, [AuthenticatableEventListener::class, 'onEvent']);
        }
    }

    /**
     * @param  array<int, string>  $trackedEvents
     */
    private function validateTrackedEvents(array $trackedEvents): void
    {
        $unknown = array_diff($trackedEvents, array_keys(self::EVENT_CLASSES));

        throw_unless(
            $unknown === [],
            InvalidArgumentException::class,
            sprintf(
                'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED names %s, which %s no authentication event. '
                .'It takes a comma-separated list of: %s. A boolean parses to the list "1" and tracks nothing; '
                .'leave it empty to switch authentication event tracking off.',
                implode(', ', array_map(fn (string $name): string => '"'.$name.'"', $unknown)),
                count($unknown) === 1 ? 'matches' : 'match',
                implode(', ', array_keys(self::EVENT_CLASSES))
            )
        );
    }
}

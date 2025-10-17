<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Models\Request;

class TrackListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Track $track): void
    {
        Log::debug('TrackListener->handle()', [$track]);

        Request::query()->create([
            'type' => 'track',
            'model_class' => $track->payload->model_class,
            'object_uid' => $track->payload->object_uid,
            'session_uid' => $track->payload->session_uid,
            'ip_address' => $track->payload->ip_address,
            'timestamp' => $track->payload->timestamp,
            'properties' => $track->payload->properties ?? [],
        ]);

        /**
         * To repond to events createa a listener class in App\Listeners
         * using the name of the event converted to CamelCase
         *
         * So:
         * i:did:a:thing => IDidAThingListener
         * i_did_a_thing => IDidAThingListener
         * IDidAThing => IDidAThingListener
         */
        Log::debug('TrackListener->handle() - listeners', [$track]);

        $listenerClass = $this->listenerName($track);

        Log::info('Listener class name: ', [$listenerClass]);

        if (! class_exists($listenerClass)) {
            Log::debug('Listener class does not exist', [$listenerClass]);

            return;
        }

        Log::debug('Listener class exists: ', [$listenerClass]);

        $listener = new $listenerClass;

        throw_unless(method_exists($listener, 'handle'), Exception::class, 'Listener class does not have handle method');

        $listener->handle($track);
    }

    /**
     * Format the name of the listener class file that -- should it exist --
     * will handle this event
     */
    public function listenerName(Track $track): string
    {
        return config('stickle.namespaces.listeners').
            '\\'.
            Str::studly(class_basename($track->payload->properties['name'] ?? 'unknown')).
            'Listener';
    }
}

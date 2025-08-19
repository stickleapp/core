<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use DateTime;
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

    public function handle(Track $event): void
    {
        Log::debug('TrackListener->handle()', [$event]);

        $request = Request::create([
            'type' => 'track',
            'model_class' => data_get($event->payload, 'model_class'),
            'object_uid' => data_get($event->payload, 'object_uid'),
            'session_uid' => data_get($event->payload, 'session_uid'),
            'ip_address' => data_get($event->payload, 'ip_address'),
            'timestamp' => data_get($event->payload, 'timestamp', new DateTime),
            'properties' => data_get($event->payload, 'properties', []),
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
        Log::debug('TrackListener->handle() - listeners', [$event]);

        $listenerClass = $this->listenerName($event);

        Log::info('Listener class name: ', [$listenerClass]);

        if (! class_exists($listenerClass)) {
            Log::debug('Listener class does not exist', [$listenerClass]);

            return;
        }

        Log::debug('Listener class exists: ', [$listenerClass]);

        $listener = new $listenerClass;

        if (! method_exists($listener, 'handle')) {
            throw new \Exception('Listener class does not have handle method');
        }

        $listener->handle($event);
    }

    /**
     * Format the name of the listener class file that -- should it exist --
     * will handle this event
     */
    public function listenerName(Track $event): string
    {
        return config('stickle.namespaces.listeners').
            '\\'.
            Str::studly(class_basename(data_get($event->payload, 'event'))).
            'Listener';
    }
}

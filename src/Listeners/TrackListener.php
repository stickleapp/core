<?php

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Events\Track;

class TrackListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepository $repository) {}

    public function handle(Track $event): void
    {
        Log::debug('TrackListener->handle()', [$event]);

        $this->repository->saveEvent(
            model: data_get($event->data, 'model'),
            objectUid: data_get($event->data, 'object_uid'),
            sessionUid: data_get($event->data, 'session_uid'),
            timestamp: data_get($event->data, 'timestamp', new DateTime),
            event: data_get($event->data, 'event'),
            properties: data_get($event->data, 'properties'),
        );

        /**
         * To repond to events createa a listener class in App\Listeners
         * using the name of the event converted to CamelCase
         *
         * So:
         * i:did:a:thing => IDidAThingListener
         * i_did_a_thing => IDidAThingListener
         * IDidAThing => IDidAThingListener
         */
        $listenerClass = config('stickle.paths.listeners').
            '\\'.
            Str::studly(class_basename(data_get($event->data, 'event'))).
            'Listener';

        Log::info('Listener class name: ', [$listenerClass]);

        if (! class_exists($listenerClass)) {
            Log::debug('Listener class does not exist', [$listenerClass]);

            return;
        }

        Log::debug('Listener class exists: ', [$listenerClass]);

        $listener = new $listenerClass;

        if (! method_exists($listener, 'handle')) {
            throw new \Exception('Listener class does not have handle method', [$listenerClass]);
        }

        $listener->handle($event);
    }
}

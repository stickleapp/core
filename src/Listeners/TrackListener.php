<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Events\Track;

class TrackListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Track $event): void
    {
        Log::debug('TrackListener->handle()', [$event]);

        $this->repository->saveEvent(
            model: data_get($event->payload, 'model_class'),
            objectUid: data_get($event->payload, 'object_uid'),
            sessionUid: data_get($event->payload, 'session_uid'),
            timestamp: data_get($event->payload, 'timestamp', new DateTime),
            properties: [
                'name' => data_get($event->payload, 'name'),
                'url' => data_get($event->payload, 'url'),
                'path' => data_get($event->payload, 'path'),
                'host' => data_get($event->payload, 'host'),
                'search' => data_get($event->payload, 'search'),
                'query_params' => data_get($event->payload, 'query_params'),
                'utm_source' => data_get($event->payload, 'utm_source'),
                'utm_medium' => data_get($event->payload, 'utm_medium'),
                'utm_campaign' => data_get($event->payload, 'utm_campaign'),
                'utm_content' => data_get($event->payload, 'utm_content'),
                'user_agent' => data_get($event->payload, 'user_agent'),
                'method' => data_get($event->payload, 'method'),
            ]
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

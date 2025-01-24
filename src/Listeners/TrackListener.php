<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use DateTime;
use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Track;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TrackListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepository $repository) {}

    public function handle(Track $event): void
    {
        Log::debug('TrackEvent Handled', [$event]);

        $this->repository->saveEvent(
            model: data_get($event->data, 'model'),
            objectUid: data_get($event->data, 'objectUid'),
            sessionUid: data_get($event->data, 'sessionUid'),
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
        $class = config('cascade.paths.listeners').'\\'.class_basename(data_get($event, 'data.event')).'Listener';

        Log::debug('TrackEvent Class', [$class]);

        if (class_exists($class)) {
            Log::debug('TrackEvent Class Exists', [$class]);
            $listener = new $class;
            if (! method_exists($listener, 'handle')) {
                throw new \Exception('TrackEvent Class $class Does Not Have Handle Method');
            }
            $listener->handle($event);
        } else {
            Log::debug('TrackEvent Class Does Not Exist', [$class]);
        }
    }
}

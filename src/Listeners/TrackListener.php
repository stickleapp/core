<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Events\Track;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TrackListener implements ShouldQueue
{
    public function handle(Track $event): void
    {
        Log::debug('TrackEvent Handled', [$event]);

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
            $listener->handle($event);
        } else {
            Log::debug('TrackEvent Class Does Not Exist', [$class]);
        }
    }
}

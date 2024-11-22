<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascadeCore\Events\Track;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrackListener implements ShouldQueue
{
    public function handle(Track $event)
    {
        /**
         * To repond to events createa a listener class in App\Listeners
         * using the name of the event converted to CamelCase
         *
         * So:
         * i:did:a:thing => IDidAThingListener
         * i_did_a_thing => IDidAThingListener
         * IDidAThing => IDidAThingListener
         */
        $class = config('cascade.paths.listeners').'\\'.class_basename($event->data['event']).'Listener';

        if (class_exists($class)) {
            $listener = new $class;
            $listener->handle($event);
        }
    }
}

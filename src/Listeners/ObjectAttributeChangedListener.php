<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ObjectAttributeChangedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    public function handle(ObjectAttributeChanged $event): void
    {
        Log::debug('ObjectAttributeChanged Handled', [$event]);

        /**
         * Look for a listener Model + Attribute + Listener
         */
        $class = $this->listenerName($event->model, $event->attribute);

        Log::debug('ObjectAttributeChanged Class', [$class]);

        if (class_exists($class)) {
            Log::debug('TrackEvent Class Exists', [$class]);
            $listener = new $class;
            if (! method_exists($listener, 'handle')) {
                throw new \Exception('$class Does Not Have Handle Method');
            }
            $listener->handle($event);
        } else {
            Log::debug('ObjectAttributeChanged Listener Does Not Exist', [$class]);
        }
    }

    private function listenerName(string $model, string $attribute): string
    {
        $model = str_replace(' ', '', ucwords(str_replace('_', ' ', $model)));
        $attribute = str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        return config('cascade.paths.listeners').'\\'.$model.$attribute.'Listener';
    }
}

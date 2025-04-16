<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ModelAttributeChanged;

class ModelAttributeChangedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    public function handle(ModelAttributeChanged $event): void
    {
        Log::debug('ModelAttributeChangedListener->handle()', [$event]);

        /**
         * Look for a listener Model + Attribute + Listener
         */
        $modelClass = $this->listenerName($event->modelClass, $event->attribute);

        Log::debug('ModelAttributeChanged Class', [$modelClass]);

        if (class_exists($modelClass)) {
            Log::debug('TrackEvent Class Exists', [$modelClass]);
            $listener = new $modelClass;
            if (! method_exists($listener, 'handle')) {
                throw new \Exception('$modelClass Does Not Have Handle Method');
            }
            $listener->handle($event);
        } else {
            Log::debug('ModelAttributeChanged Listener Does Not Exist', [$modelClass]);
        }
    }

    /**
     * Format the name of the listener class file that -- should it exist --
     * will handle this event
     */
    public function listenerName(string $modelClass, string $attribute): string
    {
        $modelClass = str_replace(' ', '', ucwords(str_replace('_', ' ', $modelClass)));

        $attribute = str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        return config('stickle.namespaces.listeners').'\\'.$modelClass.$attribute.'Listener';
    }
}

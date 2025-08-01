---
outline: deep
---

# Segment Events Listeners

When users are added to and removed from segments, `ObjectEnteredSegment` and `ObjectExitedSegment` events are dispatched. You can write listeners that respond to these events.

## Writing Listeners

To do so, create a listener class extending `Illuminate\Contracts\Queue\ShouldQueue` containing a `handle` method that takes a `ObjectEnteredSegment` or `ObjectExitedSegment` object as a parameter. Laravel will automatically execute the listener when the event is dispatched.

```php
namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendLowHealthEmail implements ShouldQueue
{

    #[ObjectAttributeShoudBe('event', 'segment.name', ['LowHealth', 'VeryLowHealth', 'NoHealth'])]
    public function handle(ObjectEnteredSegment $event): void
    {
        Log::debug('SendLowHealthEmail Handled ObjectEnteredSegment', [$event]);
    }
}
```

How do we constrain this to selected events?

-   Attributes
    -   Generic `ObjectAttributeShoudBe`
    -   Specific `SegmentNameShouldBe`
-   Implement Trait
    -   ListensToSegmentEvents (static $events = [] method)

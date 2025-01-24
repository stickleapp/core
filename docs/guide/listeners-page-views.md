---
outline: deep
---

# Page View Listeners

You can create a listener class extending `Illuminate\Contracts\Queue\ShouldQueue` containing a `handle` method that takes a `Dclaysmith\LaravelCascade\Events\Page` object as a parameter. Laravel will automatically execute the listener when a page is dispatched.

## Writing Listeners

```php
namespace App\Listeners;

use Dclaysmith\LaravelCascade\Events\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class VisitedPricingPage implements ShouldQueue
{

    public function handle(Page $event): void
    {
        Log::debug('VisitedPricingPage Handled Page', [$event]);
    }
}
```

How do we constrain this to selected events?

-   Attributes
    -   Generic `ObjectAttributeShoudBe` (page, )
    -   Specific `PageNameShouldBe`
-   Implement Trait
    -   ListensToPageEvent (static $events = [] method)

---
outline: deep
---

# Listening to `\Illuminate\Auth\Events`

There is no Stickle-specific functionality required to respond to these events.

You simply need to create a listener class and type-hint the `handle()` method to accept a `\Illuminate\Auth\[Event Name]`.

Example:

```php
namespace App\Listeners;

use Illuminate\Aut\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UserLoggedIn implements ShouldQueue
{

    public function handle(Login $event): void
    {
        Log::debug('UserLoggedIn Handled \Illuminate\Aut\Events\Login event.', [$event]);
    }
}
```

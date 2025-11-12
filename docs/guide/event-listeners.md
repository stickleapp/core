---
outline: deep
---

# Event Listeners

Stickle makes it easy to listen for and respond to events in your application. Whether users are clicking buttons, changing attributes, or entering segments, you can trigger custom logic in response.

## Events in Stickle

Stickle dispatches several types of events throughout your application:

| Event | When Dispatched | Use Cases |
|-------|----------------|-----------|
| `Track` | Custom user events | Track button clicks, feature usage, custom actions |
| `Page` | Page views | Monitor navigation, build user journeys |
| `ObjectAttributeChanged` | Model attribute changes | Alert on status changes, audit critical fields |
| `ObjectEnteredSegment` | User enters segment | Send welcome emails, trigger workflows |
| `ObjectExitedSegment` | User leaves segment | Re-engagement campaigns, alerts |
| `Illuminate\Auth\Events\*` | Authentication events | Track logins, registrations, logouts |

## User Events (Track)

Custom events allow you to track any user action in your application.

### Dispatching Custom Events

**From JavaScript:**

```javascript
// Track a button click
stickle.track('clicked:upgrade_button', {
    plan: 'pro',
    price: 99.00
});

// Track feature usage
stickle.track('used:export_feature', {
    format: 'csv',
    records: 1500
});
```

**From Server-Side:**

```php
use StickleApp\Core\Events\Track;

// Track server-side event
Track::dispatch([
    'user' => auth()->user(),
    'name' => 'completed:onboarding',
    'data' => [
        'steps_completed' => 5,
        'time_taken_minutes' => 12,
    ],
]);
```

### Listening to Custom Events

Create a listener class with a name based on the event name (converted to camel case) and suffixed with "Listener". Place it in `app/Listeners/` (or your configured listeners directory).

For an event named `clicked:upgrade_button`, create `ClickedUpgradeButtonListener`:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\Track;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ClickedUpgradeButtonListener implements ShouldQueue
{
    public function handle(Track $event): void
    {
        $user = $event->user;
        $data = $event->data;

        // Send notification to sales team
        Log::info("User {$user->email} clicked upgrade button", $data);

        // Trigger follow-up email
        // Mail::to($user)->send(new UpgradeOfferEmail($data['plan']));
    }
}
```

**Event Name Variants:**

A listener named `IDidAThingListener` will catch any of these event names:
- `i:did:a:thing`
- `i_did_a_thing`
- `IDidAThing`

## Page View Events

Track when users view specific pages in your application.

### How Page Views Work

Stickle automatically tracks page views through:
1. JavaScript SDK (for SPAs and client-side navigation)
2. Request middleware (for traditional server-rendered pages)

### Listening to Page Views

Create a listener extending `ShouldQueue` with a `handle` method accepting a `Page` event:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PricingPageViewed;

class VisitedPricingPage implements ShouldQueue
{
    public function handle(Page $event): void
    {
        // Check if user visited pricing page
        if (str_contains($event->path, '/pricing')) {
            $user = $event->user;

            // Send notification to sales team
            Notification::route('slack', config('services.slack.sales_webhook'))
                ->notify(new PricingPageViewed($user));
        }
    }
}
```

**Page Event Properties:**

```php
$event->user            // The authenticated user
$event->url             // Full URL
$event->path            // Path (e.g., '/pricing')
$event->host            // Hostname
$event->search          // Query string
$event->utm_source      // UTM source
$event->utm_medium      // UTM medium
$event->utm_campaign    // UTM campaign
$event->session_uid     // Session identifier
```

## Attribute Change Events

Monitor changes to specific model attributes and respond in real-time.

### How It Works

**Standard Attributes:**
Model attributes that exist as database columns are tracked in real-time. When a model is saved with a new value, `ObjectAttributeChanged` is dispatched immediately.

**Calculated Attributes:**
Custom or calculated attributes (defined as accessors) are updated on a schedule. When changed, an `ObjectAttributeChanged` event is dispatched.

### Listening to Attribute Changes

Create a listener with the naming convention: **ModelName** + **AttributeName** + **Listener**.

For example, to respond to changes in the User's `order_count` attribute:

1. Ensure `order_count` is in the model's `stickleObservedAttributes()` array
2. Create `App\Listeners\UserOrderCountListener`
3. Add a handler method

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\MilestoneAchieved;

class UserOrderCountListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        $user = $event->object;
        $oldValue = $event->oldValue;
        $newValue = $event->newValue;

        // Celebrate milestones
        if ($newValue == 10 && $oldValue < 10) {
            Mail::to($user)->send(new MilestoneAchieved(10));
        }

        if ($newValue == 100 && $oldValue < 100) {
            Mail::to($user)->send(new MilestoneAchieved(100));
        }
    }
}
```

**Event Properties:**

```php
$event->object          // The model instance
$event->attribute_name  // Name of the attribute that changed
$event->oldValue        // Previous value
$event->newValue        // New value
$event->changed_at      // Timestamp of change
```

## Segment Events

Respond when users enter or exit customer segments.

### ObjectEnteredSegment

Triggered when a model is added to a segment:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeToSegment;

class SendHighValueWelcome implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        // Only act on HighValueCustomers segment
        if ($event->segment->as_class === 'HighValueCustomers') {
            $user = $event->object;

            // Send VIP welcome email
            Mail::to($user)->send(new WelcomeToSegment('high-value'));
        }
    }
}
```

### ObjectExitedSegment

Triggered when a model is removed from a segment:

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectExitedSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomerAtRisk;

class AlertWhenExitingActive implements ShouldQueue
{
    public function handle(ObjectExitedSegment $event): void
    {
        // Only act on ActiveUsers segment
        if ($event->segment->as_class === 'ActiveUsers') {
            $user = $event->object;

            // Alert customer success team
            Notification::route('slack', config('services.slack.cs_webhook'))
                ->notify(new CustomerAtRisk($user));
        }
    }
}
```

**Event Properties:**

```php
$event->object      // The model instance
$event->segment     // The segment object
$event->entered_at  // When they entered (for ObjectEnteredSegment)
$event->exited_at   // When they exited (for ObjectExitedSegment)
```

## Authentication Events

Listen to Laravel's built-in authentication events.

### Tracked Authentication Events

Stickle can automatically track these `Illuminate\Auth\Events`:

- `Authenticated`
- `Login`
- `Logout`
- `Registered`
- `Verified`
- `PasswordReset`
- `CurrentDeviceLogout`
- `OtherDeviceLogout`
- `Validated`

### Configuration

Enable authentication event tracking in `config/stickle.php`:

```php
'tracking' => [
    'server' => [
        'authenticationEvents' => true,
        'authenticationEventsTracked' => [
            'Login',
            'Logout',
            'Registered',
            // Add others as needed
        ],
    ],
],
```

Or via environment variables:

```env
STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS=true
STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED=Login,Logout,Registered
```

### Listening to Auth Events

No Stickle-specific code needed - just create standard Laravel listeners:

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UserLoggedIn implements ShouldQueue
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        Log::info("User {$user->email} logged in");

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Track login in your analytics
        // ...
    }
}
```

## Real-World Examples

### Send Email When Customer Enters At-Risk Segment

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReEngagementOffer;

class SendReEngagementEmail implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        if ($event->segment->as_class === 'AtRiskCustomers') {
            $customer = $event->object;

            Mail::to($customer)
                ->send(new ReEngagementOffer($customer));
        }
    }
}
```

### Slack Notification When High-Value Customer Logs In

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\HighValueLogin;
use StickleApp\Core\Filters\Filter;

class NotifyHighValueLogin implements ShouldQueue
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Check if user is high-value
        $isHighValue = $user->newQuery()
            ->where('id', $user->id)
            ->stickleWhere(
                Filter::segment('HighValueCustomers')->isInSegment()
            )
            ->exists();

        if ($isHighValue) {
            Notification::route('slack', config('services.slack.vip_webhook'))
                ->notify(new HighValueLogin($user));
        }
    }
}
```

### Update CRM When Attribute Changes

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\CRMService;

class UserSubscriptionStatusListener implements ShouldQueue
{
    public function __construct(
        protected CRMService $crm
    ) {}

    public function handle(ObjectAttributeChanged $event): void
    {
        $user = $event->object;
        $status = $event->newValue;

        // Update CRM when subscription status changes
        $this->crm->updateContact($user->email, [
            'subscription_status' => $status,
            'updated_at' => now(),
        ]);

        // Alert team if churned
        if ($status === 'canceled') {
            // Send alert...
        }
    }
}
```

## Best Practices

1. **Use Queues** - Implement `ShouldQueue` to avoid blocking requests
2. **Keep Listeners Focused** - One listener per specific action
3. **Handle Failures** - Implement failure handling for critical listeners
4. **Test Thoroughly** - Unit test your listener logic
5. **Monitor Performance** - Track listener execution time
6. **Be Selective** - Don't listen to every event, only what you need

## Registering Listeners

Stickle automatically discovers listeners based on naming conventions. No manual registration required!

For authentication events, register them in your `EventServiceProvider` as usual:

```php
protected $listen = [
    \Illuminate\Auth\Events\Login::class => [
        \App\Listeners\UserLoggedIn::class,
    ],
];
```

## Next Steps

- **[Events Reference](/guide/events-reference)** - Complete event reference with all properties
- **[JavaScript Tracking](/guide/javascript-tracking)** - Learn how to dispatch custom events from the client
- **[Customer Segments](/guide/segments)** - Create segments that trigger events
- **[Tracking Attributes](/guide/tracking-attributes)** - Define attributes to monitor
- **[Recipes](/guide/recipes)** - More real-world listener examples

---
outline: deep
---

# Events Reference

Quick reference for all events dispatched by Stickle. For detailed usage and examples, see the [Event Listeners Guide](/guide/event-listeners).

## Event Index

| Event | When Dispatched | Namespace |
|-------|----------------|-----------|
| [Track](#track-event) | Custom user events | `StickleApp\Core\Events` |
| [Page](#page-event) | Page views | `StickleApp\Core\Events` |
| [ObjectAttributeChanged](#objectattributechanged-event) | Model attribute changes | `StickleApp\Core\Events` |
| [ObjectEnteredSegment](#objectenteredsegment-event) | Model enters segment | `StickleApp\Core\Events` |
| [ObjectExitedSegment](#objectexitedsegment-event) | Model leaves segment | `StickleApp\Core\Events` |
| [Illuminate\Auth\Events\*](#authentication-events) | Auth events | `Illuminate\Auth\Events` |

---

## Track Event

**Class:** `StickleApp\Core\Events\Track`

**When Dispatched:** Custom user-defined events tracked via JavaScript SDK or server-side.

### Properties

```php
public User $user;              // The authenticated user
public string $model_class;     // Model class name
public string $object_uid;      // Model ID
public string $name;            // Event name (e.g., "clicked:button")
public array $data;             // Event data payload
public string $session_uid;     // Session identifier
public Carbon $created_at;      // Event timestamp
```

### Example Payload

```php
[
    'user' => $user,
    'model_class' => 'App\\Models\\User',
    'object_uid' => '123',
    'name' => 'clicked:upgrade_button',
    'data' => [
        'plan' => 'pro',
        'price' => 99.00,
    ],
    'session_uid' => 'abc123',
    'created_at' => now(),
]
```

### Dispatching

**From JavaScript:**
```javascript
stickle.track('clicked:upgrade_button', { plan: 'pro', price: 99.00 });
```

**From Server:**
```php
use StickleApp\Core\Events\Track;

Track::dispatch([
    'user' => auth()->user(),
    'name' => 'completed:onboarding',
    'data' => ['steps_completed' => 5],
]);
```

---

## Page Event

**Class:** `StickleApp\Core\Events\Page`

**When Dispatched:** When a user views a page (automatic via JavaScript SDK or request middleware).

### Properties

```php
public User $user;              // The authenticated user
public string $model_class;     // Model class name
public string $object_uid;      // Model ID
public string $session_uid;     // Session identifier
public string $url;             // Full URL
public string $path;            // URL path
public string $host;            // Hostname
public ?string $search;         // Query string
public ?string $utm_source;     // UTM source
public ?string $utm_medium;     // UTM medium
public ?string $utm_campaign;   // UTM campaign
public ?string $utm_content;    // UTM content
public Carbon $created_at;      // Event timestamp
```

### Example Payload

```php
[
    'user' => $user,
    'model_class' => 'App\\Models\\User',
    'object_uid' => '123',
    'session_uid' => 'abc123',
    'url' => 'https://example.com/pricing?ref=homepage',
    'path' => '/pricing',
    'host' => 'example.com',
    'search' => 'ref=homepage',
    'utm_source' => 'google',
    'utm_medium' => 'cpc',
    'utm_campaign' => 'spring_sale',
    'utm_content' => 'text_ad',
    'created_at' => now(),
]
```

### Dispatching

**Automatic:** JavaScript SDK or request middleware handles this automatically.

**Manual:**
```javascript
stickle.page({ section: 'pricing' });
```

---

## ObjectAttributeChanged Event

**Class:** `StickleApp\Core\Events\ObjectAttributeChanged`

**When Dispatched:** When a tracked model attribute value changes.

### Properties

```php
public Model $object;           // The model instance
public string $attribute_name;  // Name of changed attribute
public mixed $oldValue;         // Previous value
public mixed $newValue;         // New value
public Carbon $changed_at;      // Change timestamp
```

### Example Payload

```php
$event->object;          // User instance
$event->attribute_name;  // 'subscription_status'
$event->oldValue;        // 'trial'
$event->newValue;        // 'active'
$event->changed_at;      // Carbon instance
```

### When It's Dispatched

- **Standard attributes:** Immediately when model is saved
- **Calculated attributes:** Periodically when value changes

### Listening

Create a listener named: `{ModelName}{AttributeName}Listener`

Example: `UserSubscriptionStatusListener` for User's `subscription_status` attribute.

---

## ObjectEnteredSegment Event

**Class:** `StickleApp\Core\Events\ObjectEnteredSegment`

**When Dispatched:** When a model is added to a segment during segment recalculation.

### Properties

```php
public Model $object;           // The model instance
public Segment $segment;        // The segment object
public Carbon $entered_at;      // When entered
```

### Example Payload

```php
$event->object;                 // User instance
$event->segment->as_class;      // 'HighValueCustomers'
$event->segment->name;          // 'High Value Customers'
$event->entered_at;             // Carbon instance
```

### Listening

```php
class SendWelcomeEmail implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        if ($event->segment->as_class === 'HighValueCustomers') {
            // Send VIP welcome email
        }
    }
}
```

---

## ObjectExitedSegment Event

**Class:** `StickleApp\Core\Events\ObjectExitedSegment`

**When Dispatched:** When a model is removed from a segment during segment recalculation.

### Properties

```php
public Model $object;           // The model instance
public Segment $segment;        // The segment object
public Carbon $exited_at;       // When exited
```

### Example Payload

```php
$event->object;                 // User instance
$event->segment->as_class;      // 'ActiveUsers'
$event->segment->name;          // 'Active Users'
$event->exited_at;              // Carbon instance
```

### Listening

```php
class AlertInactiveUser implements ShouldQueue
{
    public function handle(ObjectExitedSegment $event): void
    {
        if ($event->segment->as_class === 'ActiveUsers') {
            // Send re-engagement email
        }
    }
}
```

---

## Authentication Events

**Namespace:** `Illuminate\Auth\Events`

**When Dispatched:** Laravel authentication system events.

### Tracked Events

| Event | When Dispatched |
|-------|----------------|
| `Authenticated` | User is authenticated |
| `Login` | User logs in |
| `Logout` | User logs out |
| `Registered` | New user registers |
| `Verified` | Email verified |
| `PasswordReset` | Password reset |
| `CurrentDeviceLogout` | Logout from current device |
| `OtherDeviceLogout` | Logout from other devices |
| `Validated` | Credentials validated |

### Configuration

Enable tracking in `config/stickle.php`:

```php
'tracking' => [
    'server' => [
        'authenticationEvents' => true,
        'authenticationEventsTracked' => [
            'Login',
            'Logout',
            'Registered',
        ],
    ],
],
```

### Listening

Standard Laravel event listeners:

```php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UserLoggedIn
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        // Handle login...
    }
}
```

---

## Event Listener Naming Conventions

### Custom Events (Track)

For an event named `clicked:upgrade_button`:

Create listener: `ClickedUpgradeButtonListener`

Naming rules:
- Convert to camel case
- Suffix with "Listener"
- Place in `app/Listeners/`

### Attribute Changes

For User's `order_count` attribute:

Create listener: `UserOrderCountListener`

Format: `{ModelName}{AttributeName}Listener`

### Segments & Page Views

No naming convention - listen to event classes directly.

---

## Common Listener Patterns

### Send Email on Event

```php
class ClickedUpgradeButtonListener implements ShouldQueue
{
    public function handle(Track $event): void
    {
        $user = $event->user;
        Mail::to($user)->send(new UpgradeOffer());
    }
}
```

### Slack Notification on Segment Entry

```php
class HighValueSegmentListener implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        if ($event->segment->as_class === 'HighValueCustomers') {
            Notification::route('slack', config('slack.webhook'))
                ->notify(new NewHighValueCustomer($event->object));
        }
    }
}
```

### Update CRM on Attribute Change

```php
class UserEmailListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        $user = $event->object;
        CRM::updateContact($user->email, [
            'email' => $event->newValue,
        ]);
    }
}
```

---

## Event Timing

| Event Type | Timing | Async |
|-----------|--------|-------|
| Track | Immediate | Yes (queued) |
| Page | After response sent | Yes (queued) |
| ObjectAttributeChanged | On model save or schedule | Yes (queued) |
| ObjectEnteredSegment | During segment export | Yes (queued) |
| ObjectExitedSegment | During segment export | Yes (queued) |
| Auth Events | Immediate | Depends on listener |

## Best Practices

1. **Implement ShouldQueue** - Keep listeners async
2. **Check segment names** - Use strict comparisons for segment events
3. **Handle failures** - Implement failure handling for critical listeners
4. **Keep listeners focused** - One listener per specific action
5. **Test thoroughly** - Unit test listener logic

## Next Steps

- **[Event Listeners Guide](/guide/event-listeners)** - Detailed examples and patterns
- **[JavaScript Tracking](/guide/javascript-tracking)** - Dispatch custom events
- **[Customer Segments](/guide/segments)** - Create segments that trigger events
- **[Recipes](/guide/recipes)** - Real-world event listener examples

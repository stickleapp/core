---
outline: deep
---

# Troubleshooting

Solutions to common issues you may encounter when using Stickle. If you don't find your issue here, check the [GitHub Issues](https://github.com/stickleapp/core/issues) or create a new issue.

## Tracking Issues

### Events Not Being Tracked

**Symptoms:**
- JavaScript tracking code isn't loaded
- Events aren't appearing in StickleUI
- Database tables remain empty

**Possible Causes & Solutions:**

#### 1. Middleware Not Loaded

Check if client-side tracking middleware is enabled:

```php
// config/stickle.php
'tracking' => [
    'client' => [
        'loadMiddleware' => true, // Must be true
    ],
],
```

Or via environment:

```env
STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE=true
```

#### 2. JavaScript Not Injected

**Check if tracking code is loaded:**

View page source and search for `stickle`:

```html
<script>
    window.stickle = { /* tracking code should be here */ }
</script>
```

**If missing**, check middleware is registered:

```bash
php artisan route:list --path=stickle
```

**Clear caches:**

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### 3. User Not Authenticated

Stickle only tracks authenticated users by default.

**Verify user is logged in:**

```javascript
// In browser console
console.log(window.stickle);
// Should show tracking object with user info
```

**To track guest users**, modify the configuration:

```php
// config/stickle.php
'tracking' => [
    'requireAuthentication' => false,
],
```

#### 4. CSRF Token Issues

**Error in console:**
```
POST /stickle/api/track 419 (unknown status)
```

**Solution:**

Ensure CSRF token is included:

```blade
{{-- In your layout --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Check middleware exceptions in `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    'stickle/api/*', // Add if needed (not recommended)
];
```

#### 5. Queue Workers Not Running

Events are processed asynchronously. If queue workers aren't running, events won't be saved.

**Check queue workers:**

```bash
# Using Supervisor
sudo supervisorctl status

# Or check queue manually
php artisan queue:work --once
```

**Check for failed jobs:**

```bash
php artisan queue:failed
```

**Restart workers:**

```bash
php artisan queue:restart
sudo supervisorctl restart all
```

### Page Views Not Tracked in SPAs

**Problem:**
Client-side navigation doesn't trigger page views.

**Solution for Vue Router:**

```javascript
import { createRouter } from 'vue-router'

const router = createRouter({ /* config */ })

router.afterEach((to, from) => {
    if (window.stickle) {
        stickle.page({
            path: to.path,
            name: to.name
        });
    }
});
```

**Solution for React Router:**

```javascript
import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

function usePageTracking() {
    const location = useLocation();

    useEffect(() => {
        if (window.stickle) {
            stickle.page({
                path: location.pathname
            });
        }
    }, [location]);
}
```

**Solution for Livewire:**

```javascript
window.addEventListener('livewire:navigated', () => {
    if (window.stickle) {
        stickle.page();
    }
});
```

**Solution for Inertia:**

```javascript
import { router } from '@inertiajs/vue3'

router.on('navigate', (event) => {
    if (window.stickle) {
        stickle.page({
            url: event.detail.page.url
        });
    }
});
```

### Server-Side Tracking Creating Too Many Events

**Problem:**
Every request creates a page view event, including AJAX calls.

**Solution:**

Disable server-side middleware if using client-side tracking:

```env
STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE=false
STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE=true
```

Or add exclusions in `config/stickle.php`:

```php
'tracking' => [
    'server' => [
        'excludePaths' => [
            'api/*',
            'livewire/*',
            'webhooks/*',
        ],
    ],
],
```

## StickleUI Issues

### Dashboard Not Loading

**Symptoms:**
- 404 error when accessing `/stickle`
- Blank page
- "Route not found" error

**Solutions:**

#### 1. Check Route Registration

```bash
php artisan route:list | grep stickle
```

Should show routes like:
```
GET|HEAD  stickle ..................
GET|HEAD  stickle/{model} ..........
POST      stickle/api/track ........
```

#### 2. Clear Route Cache

```bash
php artisan route:clear
php artisan config:clear
```

#### 3. Verify Middleware

Check `config/stickle.php`:

```php
'routes' => [
    'web' => [
        'prefix' => 'stickle',
        'middleware' => ['web', 'auth'], // Must include 'web'
    ],
],
```

#### 4. Check Authentication

Ensure you're logged in and have permission to view StickleUI.

Add debug output:

```php
Route::get('/test-stickle', function () {
    return [
        'authenticated' => auth()->check(),
        'user' => auth()->user(),
        'can_view' => auth()->user()?->can('view-stickle'),
    ];
});
```

### Assets Not Loading (Styling Issues)

**Symptoms:**
- StickleUI loads but looks broken
- No styling applied
- 404 errors for CSS/JS files

**Solutions:**

#### 1. Build Assets

```bash
npm install
npm run build
```

#### 2. Publish Assets

```bash
php artisan vendor:publish --tag=stickle-assets --force
```

#### 3. Check Asset Paths

Verify `config/stickle.php`:

```php
'assets' => [
    'path' => public_path('vendor/stickle'),
    'url' => asset('vendor/stickle'),
],
```

#### 4. Clear View Cache

```bash
php artisan view:clear
```

#### 5. Check File Permissions

```bash
chmod -R 755 public/vendor/stickle
```

### Real-Time Updates Not Working

**Symptoms:**
- Dashboard doesn't update automatically
- WebSocket connection errors in console

**Solutions:**

#### 1. Check Broadcasting Configuration

```env
BROADCAST_DRIVER=reverb  # or pusher
```

#### 2. Verify Reverb Is Running

```bash
# Check if Reverb is running
sudo supervisorctl status reverb

# Or start manually for testing
php artisan reverb:start
```

#### 3. Test WebSocket Connection

Open browser console on StickleUI:

```javascript
// Should see connection status
Echo.connector.pusher.connection.state
// Should be: "connected"
```

#### 4. Check Firewall Rules

Ensure WebSocket port is open:

```bash
# For Reverb (default port 8080)
sudo ufw allow 8080
```

#### 5. Verify Nginx Configuration

If behind Nginx, ensure WebSocket proxy is configured:

```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
}
```

## Segment Issues

### Segments Not Updating

**Symptoms:**
- Segment members list is stale
- Users who should be in segment aren't showing
- Export command doesn't run

**Solutions:**

#### 1. Check Scheduled Tasks

Verify cron is running:

```bash
# Check if schedule:run is configured
crontab -l

# Should see:
# * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

#### 2. Manually Export Segments

```bash
php artisan stickle:export-segments
```

**Check output for errors:**

```bash
php artisan stickle:export-segments -v
```

#### 3. Check Segment Interval

Verify `exportInterval` in segment class:

```php
#[StickleSegmentMetadata([
    'exportInterval' => 60, // Minutes between exports
])]
class MySegment extends Segment
{
    // ...
}
```

#### 4. Verify Segment Logic

Test segment query manually:

```php
use App\Segments\MySegment;

$segment = new MySegment();
$members = $segment->toBuilder()->get();

dd($members); // Should show expected users
```

#### 5. Check Queue Processing

Segment exports are queued:

```bash
# Check queue
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed
```

### Segment Query Performance Issues

**Problem:**
Segment exports timing out or taking too long.

**Solutions:**

#### 1. Add Database Indexes

```php
Schema::table('users', function (Blueprint $table) {
    $table->index('created_at');
    $table->index('subscription_status');
});

Schema::table('stickle_events', function (Blueprint $table) {
    $table->index(['object_uid', 'name', 'created_at']);
});
```

#### 2. Optimize Query

Use eager loading and limit joins:

```php
public function toBuilder(): Builder
{
    return $this->model::query()
        ->select('users.*') // Only select needed columns
        ->with('subscription:id,user_id,status') // Eager load
        ->stickleWhere(/* filters */);
}
```

#### 3. Enable Query Caching

```php
// config/stickle.php
'cache' => [
    'segments' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
],
```

#### 4. Debug Query

Log the SQL being executed:

```php
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

$segment = new MySegment();
$members = $segment->toBuilder()->get();

dd(DB::getQueryLog());
```

## Attribute Tracking Issues

### Calculated Attributes Not Updating

**Symptoms:**
- Attribute values are stale
- Changes not reflected in StickleUI
- `ObjectAttributeChanged` events not firing

**Solutions:**

#### 1. Verify Attribute Is Tracked

Check model configuration:

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'order_count', // Must be listed here
            'mrr',
        ];
    }
}
```

#### 2. Force Recalculation

```bash
php artisan stickle:calculate-attributes --force
```

#### 3. Check Scheduled Task

Attributes are recalculated periodically:

```bash
# Verify schedule
php artisan schedule:list

# Should show:
# stickle:calculate-attributes ... Next Due: 5 minutes from now
```

#### 4. Test Accessor

Verify the accessor works:

```php
$user = User::find(1);
dd($user->order_count); // Should return correct value
```

#### 5. Check for Errors

```bash
# Watch logs while running calculation
tail -f storage/logs/laravel.log
php artisan stickle:calculate-attributes
```

### Attribute Changes Not Triggering Listeners

**Problem:**
`ObjectAttributeChanged` event not dispatched when attribute changes.

**Solutions:**

#### 1. Check Observed Attributes

For immediate tracking, add to `stickleObservedAttributes`:

```php
public static function stickleObservedAttributes(): array
{
    return [
        'subscription_status', // Tracked immediately on save
    ];
}
```

#### 2. Verify Listener Naming

Listener must follow naming convention:

- Attribute: `subscription_status`
- Model: `User`
- Listener: `UserSubscriptionStatusListener`

```php
// app/Listeners/UserSubscriptionStatusListener.php
namespace App\Listeners;

use StickleApp\Core\Events\ObjectAttributeChanged;

class UserSubscriptionStatusListener
{
    public function handle(ObjectAttributeChanged $event): void
    {
        // ...
    }
}
```

#### 3. Clear Event Cache

```bash
php artisan event:clear
composer dump-autoload
```

#### 4. Check Queue

Listeners are queued by default:

```bash
php artisan queue:work --once
```

## Performance Issues

### High Database Load

**Symptoms:**
- Slow queries
- High CPU usage on database
- Application timeouts

**Solutions:**

#### 1. Add Indexes

```bash
php artisan migrate
```

Check `database/migrations/*_add_stickle_indexes.php`:

```php
Schema::table('stickle_events', function (Blueprint $table) {
    $table->index(['object_uid', 'created_at']);
    $table->index(['session_uid', 'created_at']);
    $table->index('name');
});
```

#### 2. Archive Old Data

```bash
php artisan stickle:archive-events --older-than=90days
```

#### 3. Enable Query Caching

```php
// config/stickle.php
'cache' => [
    'queries' => [
        'enabled' => true,
        'ttl' => 300, // 5 minutes
    ],
],
```

#### 4. Optimize Tables

```bash
# MySQL
php artisan db:table optimize stickle_events
php artisan db:table optimize stickle_attribute_changes
```

#### 5. Use Read Replicas

Configure read replicas in `config/database.php`:

```php
'mysql' => [
    'read' => [
        'host' => ['192.168.1.2'],
    ],
    'write' => [
        'host' => ['192.168.1.1'],
    ],
],
```

### Queue Jobs Backing Up

**Symptoms:**
- Thousands of pending jobs
- Events delayed significantly
- Memory issues

**Solutions:**

#### 1. Increase Workers

```ini
# /etc/supervisor/conf.d/stickle-worker.conf
[program:stickle-worker]
numprocs=8  # Increase from 4 to 8
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart stickle-worker:*
```

#### 2. Optimize Job Processing

```php
// In listener
class MyListener implements ShouldQueue
{
    public $timeout = 60;
    public $tries = 3;
    public $backoff = [10, 30, 60];
}
```

#### 3. Use Job Batching

For bulk operations:

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

Bus::batch($jobs)
    ->then(function (Batch $batch) {
        // All jobs completed
    })
    ->dispatch();
```

#### 4. Prioritize Queues

```bash
# High-priority queue
php artisan queue:work --queue=high,default,low
```

#### 5. Monitor with Horizon

```bash
composer require laravel/horizon
php artisan horizon:install
```

Access dashboard at `/horizon`

### Memory Issues

**Symptoms:**
- `Allowed memory size exhausted` errors
- Worker processes crashing
- PHP-FPM restarts

**Solutions:**

#### 1. Increase PHP Memory Limit

```ini
# php.ini
memory_limit = 512M
```

#### 2. Restart Workers Regularly

```ini
# Supervisor config
[program:stickle-worker]
command=php artisan queue:work --max-time=3600
```

#### 3. Chunk Large Queries

```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process...
    }
});
```

#### 4. Release Memory

```php
// In long-running jobs
gc_collect_cycles();
```

#### 5. Monitor Memory Usage

```bash
# Watch memory usage
watch -n 1 'ps aux | grep queue:work'
```

## Debugging Tips

### Enable Debug Mode Temporarily

```php
// In a specific controller or command
config(['app.debug' => true]);

// Or for testing
APP_DEBUG=true php artisan stickle:export-segments
```

### Log Queries

```php
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    Log::info('Query', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time,
    ]);
});
```

### Test Event Dispatching

```php
use StickleApp\Core\Events\Track;
use Illuminate\Support\Facades\Event;

Event::fake();

Track::dispatch([
    'user' => auth()->user(),
    'name' => 'test:event',
    'data' => ['test' => true],
]);

Event::assertDispatched(Track::class);
```

### Check Package Version

```bash
composer show stickleapp/core
```

### Verify Configuration

```php
// In tinker
php artisan tinker

>>> config('stickle')
// Shows entire configuration
```

## Getting Help

If you're still experiencing issues:

1. **Check the documentation:**
   - [Installation Guide](/guide/installation)
   - [Configuration Reference](/guide/configuration)
   - [Deployment Guide](/guide/deployment)

2. **Search GitHub Issues:**
   - [Stickle Core Issues](https://github.com/stickleapp/core/issues)

3. **Create a new issue:**
   - Include error messages
   - Provide configuration files
   - Describe steps to reproduce
   - Include Laravel and PHP versions

4. **Join the community:**
   - Discord: [Join our server](#)
   - Twitter: [@stickleapp](#)

## Next Steps

- **[Deployment Guide](/guide/deployment)** - Production best practices
- **[Configuration](/guide/configuration)** - All configuration options
- **[API Endpoints](/guide/api-endpoints)** - API reference
- **[Recipes](/guide/recipes)** - Working code examples

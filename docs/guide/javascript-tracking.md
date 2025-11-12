---
outline: deep
---

# Tracking: Client and Server

Stickle provides two methods for tracking user behavior: client-side JavaScript tracking and server-side middleware tracking. Understanding when to use each method helps you build comprehensive analytics.

## Overview

| Method | Best For | Pros | Cons |
|--------|----------|------|------|
| **JavaScript SDK** | SPAs, client-side navigation, custom events | Tracks client-side interactions, works with SPAs, no page reloads needed | Requires JavaScript, can be blocked by ad blockers |
| **Server Middleware** | Traditional apps, API tracking | Always works, tracks server requests, no client dependencies | Creates noise for SPAs, tracks every request |

## Client-Side Tracking (JavaScript SDK)

Stickle can automatically inject a lightweight JavaScript tracking code into your Laravel application.

### Configuration

Enable client-side tracking in `config/stickle.php`:

```php
'tracking' => [
    'client' => [
        'loadMiddleware' => true,  // Inject tracking code
    ],
],
```

Or via environment variable:

```env
STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE=true
```

### How It's Injected

When enabled, Stickle automatically injects the tracking script into your HTML responses. The script is lightweight and non-blocking.

### Available Methods

#### `stickle.page()`

Track a page view. Called automatically when users navigate, but you can call it manually:

```javascript
// Track current page
stickle.page();

// Track with additional data
stickle.page({
    section: 'documentation',
    category: 'getting-started'
});
```

#### `stickle.track()`

Track custom events with optional data:

```javascript
// Basic event
stickle.track('clicked:button');

// Event with data
stickle.track('clicked:upgrade', {
    plan: 'pro',
    price: 99.00,
    currency: 'USD'
});

// Track feature usage
stickle.track('used:export_feature', {
    format: 'csv',
    records: 1500,
    filters_applied: 3
});
```

### SPA Integration

#### Livewire

Stickle works automatically with Livewire. Page views are tracked as users navigate between Livewire components.

**Optional**: Track Livewire-specific events:

```javascript
// In your Livewire component
window.addEventListener('livewire:navigated', () => {
    stickle.page();
});
```

#### Inertia.js

Track page visits in Inertia apps:

```javascript
// In your app.js
import { router } from '@inertiajs/vue3'

router.on('navigate', (event) => {
    stickle.page({
        url: event.detail.page.url
    });
});
```

#### Vue.js

Track navigation in Vue Router:

```javascript
// In your router setup
import { createRouter } from 'vue-router'

const router = createRouter({
    // ... your routes
})

router.afterEach((to, from) => {
    stickle.page({
        path: to.path,
        name: to.name
    });
});

export default router
```

#### React

Track navigation in React Router:

```javascript
// In your App.js or router setup
import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

function usePageTracking() {
    const location = useLocation();

    useEffect(() => {
        stickle.page({
            path: location.pathname,
            search: location.search
        });
    }, [location]);
}

// Use in your App component
function App() {
    usePageTracking();
    // ... rest of app
}
```

## Server-Side Tracking (Middleware)

Stickle includes a `RequestLogger` middleware that automatically tracks page views for authenticated users.

### Configuration

Enable server-side tracking in `config/stickle.php`:

```php
'tracking' => [
    'server' => [
        'loadMiddleware' => true,  // Enable request logging
    ],
],
```

Or via environment variable:

```env
STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE=true
```

### How It Works

The middleware captures authenticated requests and dispatches `Page` events containing:

- User information
- URL details (full URL, path, host, query string)
- UTM parameters (source, medium, campaign, content)
- Session ID for user journey tracking
- Timestamp

**Performance Note:**
The middleware uses Laravel's `terminate()` method, so tracking happens *after* the response is sent to the user. Your application remains fast.

### Data Collected

```php
Page::dispatch([
    'user' => $request->user(),
    'model_class' => get_class($request->user()),
    'object_uid' => (string) $request->user()->id,
    'session_uid' => $request->session()->getId(),
    'url' => $request->fullUrl(),
    'path' => $request->getPathInfo(),
    'host' => $request->getHost(),
    'search' => $request->getQueryString(),
    'utm_source' => $request->query('utm_source'),
    'utm_medium' => $request->query('utm_medium'),
    'utm_campaign' => $request->query('utm_campaign'),
    'utm_content' => $request->query('utm_content'),
    'created_at' => now(),
]);
```

### Ignored Requests

The middleware automatically ignores:

- **Unauthenticated requests** - Only tracks logged-in users
- **Livewire AJAX requests** - Avoids duplicate tracking
- **Development tools** - Telescope, Horizon
- **Health checks** - System monitoring endpoints

**Ignored URL patterns:**

```php
'livewire/*'
'*/livewire/*'
'telescope/*'
'horizon/*'
'health'
'ping'
```

## When to Use Which Method

### Use Client-Side Tracking When:

- Building a **Single Page Application (SPA)**
- Need to track **client-side interactions** (clicks, form submissions)
- Want to track **user engagement** (time on page, scroll depth)
- Using **client-side navigation** (Vue Router, React Router)
- Need **real-time event tracking**

### Use Server-Side Tracking When:

- Building a **traditional Laravel application**
- Need **comprehensive server-side tracking**
- Want to track **API usage patterns**
- JavaScript may be disabled or blocked
- Need **guaranteed tracking** (no client dependencies)

### Use Both When:

- Building a **hybrid application** (some server-rendered, some SPA pages)
- Want **maximum coverage** of user behavior
- Need both **server requests** and **client interactions**

::: tip Recommendation
For most modern Laravel applications, use **client-side tracking** for page views and user interactions, supplemented with **custom server-side events** for important backend actions.
:::

## Custom Server-Side Events

Even if using client-side tracking, you can dispatch custom server-side events:

```php
use StickleApp\Core\Events\Track;

// Track server-side action
Track::dispatch([
    'user' => $user,
    'name' => 'completed:checkout',
    'data' => [
        'amount' => $order->total,
        'items' => $order->items->count(),
        'payment_method' => $paymentMethod,
    ],
]);
```

## Debugging Tracking

### Check if tracking code is loaded

View page source and search for `stickle`:

```html
<script>
    window.stickle = { /* tracking code */ }
</script>
```

### Check JavaScript console

Open browser console and test:

```javascript
stickle.track('test:event', { test: true });
// Should see network request to /stickle/api/track
```

### Monitor in StickleUI

Navigate to `/stickle/events` to see real-time event stream.

### Check logs

Enable debug logging:

```php
// config/logging.php
'channels' => [
    'stickle' => [
        'driver' => 'daily',
        'path' => storage_path('logs/stickle.log'),
        'level' => 'debug',
    ],
],
```

## Best Practices

1. **Start with client-side tracking** for most applications
2. **Disable server middleware** for SPAs to avoid noise
3. **Use meaningful event names** like `clicked:upgrade_button` not just `click`
4. **Include relevant data** with events for better analysis
5. **Test in incognito mode** to ensure tracking works without cookies
6. **Monitor performance** - tracking should not slow down your app

## Configuration Reference

### Client-Side Options

```php
'tracking' => [
    'client' => [
        // Inject tracking JavaScript
        'loadMiddleware' => true,
    ],
],
```

### Server-Side Options

```php
'tracking' => [
    'server' => [
        // Enable request logging middleware
        'loadMiddleware' => true,

        // Track authentication events
        'authenticationEvents' => true,
        'authenticationEventsTracked' => [
            'Login',
            'Logout',
            'Registered',
        ],
    ],
],
```

## Next Steps

- **[Event Listeners](/guide/event-listeners)** - Respond to tracked events
- **[API Endpoints](/guide/api-endpoints)** - Use the tracking API directly
- **[Recipes](/guide/recipes)** - Real-world tracking examples
- **[Troubleshooting](/guide/troubleshooting)** - Debug tracking issues

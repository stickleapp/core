---
outline: deep
---

# Request Middleware

Stickle includes a `RequestLogger` middleware that automatically tracks page views and user navigation for authenticated users. This middleware is particularly useful for traditional Laravel applications and API tracking.

## Overview

The `RequestLogger` middleware captures page views by logging authenticated requests and dispatching `Page` events containing user context, URL information, and UTM parameters. The middleware uses Laravel's terminate method to ensure tracking doesn't slow down user requests.

## Configuration

Enable the middleware in your configuration:

```php
// config/stickle.php
'tracking' => [
    'server' => [
        'loadMiddleware' => true, // Enable request logging middleware
    ],
],
```

**Note:** This can create unwanted noise for single-page applications where client-side tracking may be more appropriate.

## How It Works

### Request Flow

1. **Handle Phase**: The middleware passes the request through without any processing
2. **Terminate Phase**: After the response is sent to the browser, the middleware logs the page view

### Data Collected

For each authenticated request, the middleware captures:

-   **User Information**: User model and ID
-   **URL Details**: Full URL, path, host, and query parameters
-   **UTM Parameters**: Source, medium, campaign, and content tracking
-   **Session Data**: Session ID for user journey tracking
-   **Timestamps**: Request time for analytics

### Event Dispatching

The middleware dispatches a `Page` event with the collected data:

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
    'created_at' => new Carbon,
    'updated_at' => new Carbon,
]);
```

## Ignored Requests

The middleware automatically ignores certain types of requests to reduce noise:

### Request Types Ignored

-   **Unauthenticated requests**: Only tracks authenticated users
-   **Livewire requests**: AJAX requests from Livewire components
-   **Development tools**: Telescope and Horizon admin interfaces
-   **Health checks**: System monitoring endpoints

### URL Patterns Ignored

```php
protected array $ignoredPatterns = [
    // Livewire
    'livewire/*',
    '*/livewire/*',

    // Telescope
    'telescope/*',
    'vendor/telescope/*',

    // Horizon
    'horizon/*',
    'vendor/horizon/*',

    // Health checks
    'health',
    'ping',
];
```

### Detection Methods

The middleware uses multiple methods to identify requests to ignore:

-   **Livewire Detection**: Checks for `X-Livewire` header, livewire routes, or `/livewire/message` paths
-   **Telescope Detection**: Looks for `X-Telescope-Request` header
-   **Pattern Matching**: Uses Laravel's `$request->is()` method for URL patterns

## Performance Considerations

### Terminate Method

The middleware uses Laravel's `terminate()` method to perform tracking after the response is sent:

```php
public function handle(Request $request, Closure $next)
{
    return $next($request); // Pass through immediately
}

public function terminate(Request $request, $response): void
{
    // Tracking happens here, after response is sent
}
```

This ensures that:

-   User requests are not slowed down by tracking logic
-   Database writes happen asynchronously
-   The user experience remains fast

### Event-Driven Architecture

The middleware dispatches events rather than directly writing to the database, allowing for:

-   Asynchronous processing via queue workers
-   Multiple listeners for the same page view data
-   Decoupled analytics processing

## Use Cases

### Traditional Laravel Applications

Ideal for server-rendered applications where:

-   Users navigate between full page loads
-   You want comprehensive server-side tracking
-   Client-side JavaScript tracking is not feasible

### API Tracking

Useful for tracking API usage patterns:

-   Monitor authenticated API endpoint usage
-   Track user API consumption patterns
-   Analyze API request flows

### Hybrid Applications

Can complement client-side tracking in applications that use both:

-   Server-rendered pages with client-side interactions
-   Mixed SPA and traditional page navigation
-   Progressive web application architectures

## Integration with Analytics

The `Page` events dispatched by this middleware integrate with Stickle's broader analytics system:

-   **Segment Analysis**: Page views contribute to user segment calculations
-   **User Journey Tracking**: Session IDs enable cross-page user flow analysis
-   **UTM Attribution**: Campaign tracking data feeds into conversion analytics
-   **Behavioral Patterns**: Request patterns inform user engagement metrics

---
outline: deep
---

# `\Illuminate\Auth\Events`

Stickle can optionally track occurrences of events emitted by Laravel's `Auth` system.

By default, Stickle tracks the following events:

-   Authenticated
-   CurrentDeviceLogout
-   Login
-   Logout
-   OtherDeviceLogout
-   PasswordReset
-   Registered
-   Validated
-   Verified

## Configuration

### Via Environment Variable

Set the `STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED` environment variable with a comma-separated list of events:

```env
STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED=Login,Logout,Registered
```

By default, all available authentication events are tracked. To disable authentication event tracking entirely, set the array to empty:

```php
'authenticationEventsTracked' => [],
```

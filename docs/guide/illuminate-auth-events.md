---
outline: deep
---

# `\Illuminate\Auth\Events`

Stickle can optionally track occurrences of events emitted by Laravel's `Auth` system. You can enable this by setting `config('stickle.tracking.server.trackAuthenticationEvents')` to `TRUE`. The following events will be logged:

-   Authenticated
-   CurrentDeviceLogout
-   Login
-   Logout
-   OtherDeviceLogout
-   PasswordReset
-   Registered
-   Validated
-   Verified

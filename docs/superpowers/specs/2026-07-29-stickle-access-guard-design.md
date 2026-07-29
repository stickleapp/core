# Require an Access Guard for Stickle Routes

**Date:** 2026-07-29
**Status:** Approved, ready for implementation planning
**Supersedes:** `2026-07-28-stickle-access-guard-design.md`

## The whole design, for someone installing this

To enable Stickle, define this Gate in `AppServiceProvider::boot()`:

```php
Gate::define('viewStickle', fn (User $user) => $user->is_admin);          // a column
Gate::define('viewStickle', fn (User $user) => $user->hasRole('admin'));  // spatie/laravel-permission
Gate::define('viewStickle', fn (User $user) => $user->isPlatformAdmin()); // an existing method
```

Until you do, `/stickle` is closed to everyone. Defining nothing is a valid choice; it leaves Stickle closed.

That is the entire operator-facing surface. Everything below is internal.

## Problem

Every Stickle route is effectively public. Verified against the current tree:

- `routes/web.php:12` groups the entire UI — `/stickle/live`, `/stickle/{modelClass}/segments`, per-record detail pages — under `config('stickle.routes.web.middleware', [])`. The config file defaults that key to `['web']`, which is session and cookie plumbing, not authorization.
- `routes/api.php:18` groups the read API — `/segments`, `/models`, `/models-statistics`, `/model-attribute-audit`, and others — under `['api']`. Same problem.
- `routes/channels.php` authorizes all three broadcast channels with an unconditional `return true`. The `is_admin` check is present but commented out.
- `CoreServiceProvider` never calls `mergeConfigFrom`; it only publishes `config/stickle.php`. In an app that has not published the config, `config('stickle.routes.web.middleware', [])` falls through to the inline default `[]` — so the UI runs with **no middleware at all**, not even `web`.

A secondary defect compounds this. `env('STICKLE_API_MIDDLEWARE', ['api'])` returns a string once the environment variable is set, so `STICKLE_API_MIDDLEWARE="auth,can:admin"` registers a single middleware named `auth,can:admin`, which does not exist. The attempt to secure the package fails silently. `stickle:install` is what puts these keys in `.env`, and it writes them as single strings (`InstallCommand.php:183,198`), so the installer produces the format that breaks as soon as a second middleware is added.

## Goal

Stickle refuses to serve its UI, its read API, and its live data stream until the host application says who may see them. There is no configuration value that opens it — only the Gate.

Event ingestion keeps working without a Gate, because the browser tracking snippet posts from unauthenticated visitors.

## Design

### 1. Authorization is the stock `can:` middleware

Guarded route groups append `can:viewStickle`. No package-specific middleware class, and no `Gate::has()` check, because Laravel already fails closed: an ability that was never defined falls through `Gate::resolveAuthCallback()` to a closure returning `null`, which is falsy, so `Gate::allows('viewStickle')` on an undefined Gate is `false`. Deny-by-default is framework behavior, not something this package implements.

The refusal is therefore Laravel's own 403. An earlier draft added an `EnsureStickleAccess` middleware purely so the body could distinguish "no Gate defined" from "Gate defined and denies." That is a diagnostic read once per installation, and it does not justify a class of ours in the request path. The distinction lives in the README and the upgrade note instead.

**The guard is appended by the route file, not read from config.** `can:viewStickle` is not part of any configurable middleware list. There is no value of `STICKLE_WEB_MIDDLEWARE` or `STICKLE_API_MIDDLEWARE` — including empty, including unset — that removes it. This is what makes the original misconception structurally impossible rather than merely documented against.

### 2. The transport keys stay, and become plain arrays

`routes.web.middleware` and `routes.api.middleware` are retained. They exist so an application can add plumbing the package cannot anticipate — most usefully `auth`, so a signed-out visitor gets a login redirect rather than a bare 403. Hardcoding `['web']` and `['api']` would remove that option for no gain, since the guard no longer lives there.

They stop using `env()`:

```php
'web' => [
    'prefix' => env('STICKLE_WEB_PREFIX', 'stickle'),
    'middleware' => ['web'],
],
```

Being prescriptive is the fix for the `env()` defect, rather than parsing around it. A middleware list is an array; arrays belong in a config file; `env()` can only return a scalar. An application that wants `auth` edits the published `config/stickle.php` and writes `['web', 'auth']`. There is no string to split, so there is no comma case to get wrong.

**No `Support\Access` class.** An earlier draft added one to normalize `null`, `''`, `[]`, strings and arrays. That reimplements the framework: `RouteRegistrar::attribute()` already applies `array_filter(Arr::wrap($value))` to the middleware key, so `Route::middleware()` accepts all of those natively. The route files need only merge the guard onto whatever config supplies — chaining `->middleware()` twice replaces rather than appends, so it must be one call.

`stickle:install` stops prompting for both keys and stops writing `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` to `.env`. Those prompts are where "middleware" got conflated with "security," and they write the scalar format that breaks.

No new config key is introduced. An earlier draft added `access.middleware`; a second middleware key would reinforce the very reading — that configuring middleware is what secures the package — that produced the current hole.

### 3. Route registration

Routes are always registered. The previous draft omitted them when unconfigured, producing a 404 indistinguishable from a typo and requiring `routes/setup.php` catch-alls whose registration order relative to ingest was load-bearing. All of that is dropped.

`routes/api.php` keeps one file. Every read endpoint is guarded; `POST /track` is not. How that exemption is expressed — a nested group, per-route middleware, `withoutMiddleware` — is left to implementation, under one constraint: it must be impossible to add a read endpoint that lands outside the guard by default. The test asserting `/track` returns 200 under all three Gate states is what holds it honest.

| Request | Gate undefined | Gate denies | Gate allows |
|---|---|---|---|
| `GET /stickle/live` | 403 | 403 | UI |
| `GET /stickle/api/segments` | 403 | 403 | JSON |
| `POST /stickle/api/track` | 200 | 200 | 200 |

### 4. Broadcast channels

The three `return true` authorizers in `routes/channels.php` become `Gate::allows('viewStickle', ...)` calls, so HTTP and WebSocket share one rule with nothing to keep in sync. The same deny-by-default applies: an undefined Gate rejects the subscription.

As shipped, each authorizer accepts its route parameters — Laravel requires the closure signature to match the channel pattern — but forwards only the user to the Gate. An earlier draft of this section forwarded the model class and id as context; that was removed before release (issue #36) because a `viewStickle` written for the HTTP routes, which have no such context, would then behave differently on the two transports for no real gain: the same data is reachable through the read API, which never receives them either.

One consequence worth naming: a user who passes the HTTP guard on an application that defines `viewStickle` will also pass the channel check, so the two cannot drift apart. That is the point of using one ability for both.

## Known limitation: no tenant scoping

Stickle has no concept of a tenant. `grep -rni tenant src/` returns nothing: no query, segment, or broadcast channel name is scoped by one.

The Gate answers "may this user open Stickle?" It cannot answer "whose data may they see?", because there is no scoping to enforce. In a multi-tenant application, anyone the Gate allows sees **every** tenant's users, events, sessions and per-record detail pages — not their own tenant's.

Stickle is therefore administrator-only in a multi-tenant application, whatever the Gate says. `/stickle` cannot safely be handed to a tenant's own staff. The documented Gate example says so, so this is discovered while writing the rule rather than while demonstrating the UI to a customer.

Real tenant scoping would touch every query, the segment builder, and the channel names. It is separate work.

## Resolved: broadcast channels are now authorized

Found during implementation of this design, fixed in the follow-up tracked as issue #35.

§4's claim that "HTTP and WebSocket share one rule with nothing to keep in sync" was wrong in practice when this design shipped, though the code it described was correct as far as it went. `routes/channels.php` did call `Gate::allows('viewStickle')`, exactly as designed — but every event in `src/Events/` broadcast on `new Channel(...)`, a public channel. Laravel (via pusher-js) only sends a subscription through `/broadcasting/auth`, and therefore through those closures, for names prefixed `private-` or `presence-`, so nothing ever invoked them and the realtime stream was open to anyone holding the application's Reverb/Pusher app key. The test that was meant to cover this called the closures directly via `Broadcast::driver()->getChannels()`, proving their logic but not their reachability, which is how it survived six task reviews.

All eight events now broadcast on `PrivateChannel` and the UI subscribes with `Echo.private()`, so §4's claim holds: one `viewStickle` ability, both transports. Two consequences worth recording:

- The authorizer patterns had to be rewritten. The channel names in config are `sprintf()` formats (`stickle.object.%s.%s`), and `%s` is not a Laravel channel wildcard, so the object and class patterns matched no name at all. They are now registered as `stickle.object.{model}.{id}` and `stickle.class.{model}`, derived from the same config values. While the channels were public this was invisible; once private it would have denied every object and class subscription.
- Authorization only happens where the application put an endpoint for it. Stickle does not call `Broadcast::routes()` on an application's behalf, so the requirement is documented instead — without it every subscription is refused and the UI degrades to polling the read API, which the same Gate guards.

`tests/Feature/Access/ChannelGuardTest.php` now drives `/broadcasting/auth` over HTTP with the channel names taken from the events themselves, so making the channels public again fails the suite.

## Testing

Pest, written failing first per test-driven development.

| Test | Asserts |
|---|---|
| `GET /stickle/live`, no Gate defined | 403 |
| `GET /stickle/live`, Gate denies | 403 |
| `GET /stickle/live`, Gate allows | 200 |
| `GET /stickle/api/segments`, no Gate defined | 403 |
| `POST /stickle/api/track` | 200 under all three Gate states |
| Guard is not configurable away | with `stickle.routes.web.middleware` set to `[]` and no Gate defined, `GET /stickle/live` still returns 403 |
| Transport middleware still applies | with it set to `['web', 'auth']` and no authenticated user, `GET /stickle/live` redirects to login rather than returning 403 |
| `POST /broadcasting/auth`, unauthenticated | 403 — fails if the channels are public, because Laravel stops guarding them |
| `POST /broadcasting/auth`, no Gate defined | 403 |
| `POST /broadcasting/auth`, Gate denies | 403 |
| `POST /broadcasting/auth`, Gate allows | 200, for the firehose, object and class channel names |
| `broadcastOn()` | every event in `src/Events/` returns `PrivateChannel` instances only |

`tests/TestCase.php::getEnvironmentSetUp` defines an allowing `viewStickle` Gate so existing UI and API tests keep passing. `workbench/` defines one so the development server keeps working.

## Upgrade path

Breaking, in two ways.

**The Gate.** Existing installations get 403 until they define it, including installations that already set `STICKLE_WEB_MIDDLEWARE=auth` — the package cannot tell a guard from plumbing, and every install should say who may enter.

**The env vars.** `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` stop being read and become inert. Any installation relying on them must move the value into the published `config/stickle.php` as an array. An installation that set them to the defaults — which is what `stickle:install` wrote — sees no behavioral change, so for most this is a stale `.env` line rather than a migration. The upgrade note must still state it plainly, because a silently ignored environment variable is exactly the failure mode this spec exists to remove.

Ships alongside: a CHANGELOG entry, an `UPGRADE.md` section carrying both changes, and a rewrite of the `STICKLE_WEB_MIDDLEWARE` / `STICKLE_API_MIDDLEWARE` entries in `docs/guide/configuration.md` describing the config keys as transport plumbing and pointing at the Gate for authorization.

## Out of Scope

**Tenant scoping.** Documented above as a known limitation.

**A distinguishing 403 body, `php artisan about` reporting, and a published service provider.** Each was in an earlier draft. The Gate definition is a one-time step documented in the README and upgrade note; none of these earn a place in the request path or the publish set. All are additive later if the plain 403 proves insufficient in practice.

**Adding `mergeConfigFrom` to `CoreServiceProvider`.** It would change config defaults package-wide with real blast radius across existing tests and installs. This design does not need it: authorization comes from the Gate, not config, so unpublished config cannot open anything. Tracked separately as a latent issue for other config-gated features.

**Authenticating the ingest endpoint.** `POST /track` stays public, matching how the browser snippet uses it. A write-key or signed-request scheme is a plausible future addition.

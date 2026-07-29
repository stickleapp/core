# Require an Access Guard for Stickle Routes

**Date:** 2026-07-28
**Status:** SUPERSEDED by `2026-07-29-stickle-access-guard-design.md`. Retained for the problem
statement, which is unchanged. The design below was not implemented — routes are no longer omitted
when unconfigured, there is no setup page, no `routes/ingest.php` split, and no `access.middleware`
config key. Authorization is the `viewStickle` Gate alone.

## Problem

Every Stickle route is effectively public by default.

- `routes/web.php:12` groups the entire UI — `/stickle/live`, `/stickle/{modelClass}/segments`, per-record detail pages — under `config('stickle.routes.web.middleware', [])`. The config file defaults that key to `['web']`, which is session and cookie plumbing, not authorization.
- `routes/api.php:18` groups the read API — `/segments`, `/models`, `/models-statistics`, `/model-attribute-audit`, and others — under `['api']`. Same problem.
- `routes/channels.php` authorizes all three broadcast channels with an unconditional `return true`. The `is_admin` check is present but commented out.
- `CoreServiceProvider` never calls `mergeConfigFrom`; it only publishes `config/stickle.php`. In any app that has not published the config, `config('stickle.routes.web.middleware', [])` falls through to the inline default `[]` — so the UI runs with **no middleware at all**, not even `web`.

A secondary defect compounds this. `env('STICKLE_API_MIDDLEWARE', ['api'])` can only return a string once the environment variable is set, so an operator who writes `STICKLE_API_MIDDLEWARE="auth,can:admin"` gets a single middleware named `auth,can:admin`, which does not exist. The attempt to secure the package silently fails.

## Goal

Stickle must refuse to expose its UI, its read API, and its live data stream until the host application has explicitly named an authorization guard. When no guard is configured, the routes are absent rather than broken — a 404 in production, and a self-explanatory setup page in development.

Event ingestion must keep working without a guard, because the browser tracking snippet posts from unauthenticated visitors.

## Design

### 1. Config surface and resolution

A new top-level `access` block in `config/stickle.php`, sibling to `routes`:

```php
'access' => [
    /*
     | REQUIRED. Middleware guarding the Stickle UI and its read API.
     | Until this is set, those routes are not registered at all.
     | Recommended: ['auth', 'can:viewStickle']
     */
    'middleware' => env('STICKLE_ACCESS_MIDDLEWARE'),
],
```

The new key is deliberately separate from `routes.web.middleware` and `routes.api.middleware`. Those keep their existing meaning — transport plumbing — and their existing defaults. `access.middleware` means "who is allowed in," has no default, and has no safe default. Reusing the existing keys would not work: they are already non-empty, so a "non-empty middleware" check would pass today and change nothing.

A new `StickleApp\Core\Support\Access` class owns all interpretation, so no route file or service provider parses config itself:

- `Access::middleware(): array` — normalizes `null`, `''`, and `[]` to `[]`; splits a string on commas with trimming and empty-filtering; passes an array through. The same normalizer is applied to `routes.web.middleware` and `routes.api.middleware`, which fixes the `env()` string defect described above.
- `Access::enabled(): bool` — `Access::middleware() !== []`.
- `Access::routeMiddleware(string $group): array` — the normalized `routes.{$group}.middleware` list, so route files get the same string-splitting treatment for transport middleware.
- `Access::allows(?Authenticatable $user, array $context = []): bool` — `false` unless `Gate::has('viewStickle')`, otherwise `Gate::forUser($user)->allows('viewStickle', $context)`. Used by the channel authorizers in section 3.

The recommended config value pairs with a single Gate the application defines once:

```php
// AppServiceProvider::boot()
Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

`can:viewStickle` in the middleware list guards HTTP; the same `viewStickle` Gate guards the broadcast channels. One authorization rule, two transports, nothing to keep in sync.

### 2. Route registration and the setup page

`routes/api.php` splits in two. `POST /track` moves to a new `routes/ingest.php` carrying only `routes.api.middleware` plus throttling. The read endpoints stay in `routes/api.php` and gain the guard.

`CoreServiceProvider::boot()` replaces its three unconditional `loadRoutesFrom` calls with:

```php
$this->loadRoutesFrom(__DIR__.'/../routes/ingest.php');    // always
$this->loadRoutesFrom(__DIR__.'/../routes/channels.php');  // always; Gate decides

if (Access::enabled()) {
    $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
} elseif (config('app.debug')) {
    $this->loadRoutesFrom(__DIR__.'/../routes/setup.php');
}
```

Guarded groups compose both middleware lists:

```php
Route::middleware([
    ...Access::routeMiddleware('web'),   // normalized routes.web.middleware
    ...Access::middleware(),
])->prefix(config('stickle.routes.web.prefix', 'stickle'))->group(...);
```

**Ingest loads first, deliberately.** `routes/setup.php` registers catch-alls under both prefixes using `->any('{any?}')->where('any', '.*')`. Laravel matches routes in registration order, so `POST /stickle/api/track` must already be on the router before the catch-all exists, or the setup page would swallow live tracking traffic. This ordering is load-bearing and has a dedicated test.

**The development check is `config('app.debug')`, not `app()->environment('local')`.** Environment names vary across teams — `dev`, `development`, `docker` — while `debug` is the established Laravel signal for "show me diagnostics" and is off in production by design. The tradeoff is that an application running `APP_DEBUG=true` in production reveals that Stickle is installed; such an application is already leaking stack traces, so this is not the marginal risk.

**The setup route returns 503, not 404.** The condition is "configured incompletely," not "no such URL." The web prefix renders `stickle::pages/setup`, a plain Blade page with no layout and no data access, styled with the package's existing Tailwind build, showing the exact config block and environment variable to set. The API prefix returns `{"message": "Stickle access guard is not configured."}` at 503, so the UI's `fetch` calls surface a readable error instead of attempting to parse HTML.

Resulting behavior:

| Request | Guard set | Guard unset, debug off | Guard unset, debug on |
|---|---|---|---|
| `GET /stickle` | UI, behind guard | 404 | 503 setup page |
| `GET /stickle/api/segments` | JSON, behind guard | 404 | 503 JSON |
| `POST /stickle/api/track` | 200 | 200 | 200 |

### 3. Broadcast channels

`routes/channels.php` continues to load unconditionally, but the three `return true` authorizers become Gate checks:

```php
Broadcast::channel(config('stickle.broadcasting.channels.firehose'), function ($user): bool {
    return Access::allows($user);
});

Broadcast::channel(config('stickle.broadcasting.channels.object'), function ($user, $model, $id): bool {
    return Access::allows($user, [$model, $id]);
});

Broadcast::channel(config('stickle.broadcasting.channels.class'), function ($user, $model): bool {
    return Access::allows($user, [$model]);
});
```

The `Gate::has('viewStickle')` check inside `Access::allows` is what makes this safe without a package-side `Gate::define` default. Defining a default in the service provider would create a race with the host application's `AppServiceProvider` over who defines `viewStickle` last. Auto-discovered package providers currently boot first, so the application would win, but that is an implementation detail this design should not depend on. Channel callbacks run at request time, long after every provider has booted, so `Gate::has` is simply accurate there. No Gate defined means deny.

Passing `[$model, $id]` lets an application scope access per record via `fn ($user, $model = null, $id = null) => ...` without requiring it. PHP silently ignores extra arguments passed to a closure, so an existing `fn ($user) => $user->is_admin` keeps working unchanged.

One consequence is worth naming: `/stickle/live` will render for a user who passes the HTTP guard, but if that application never defines `viewStickle`, the WebSocket subscription is rejected and the page sits empty. The setup page and the install command output both call this out. It is also why `['auth', 'can:viewStickle']` is the recommended middleware value rather than a bare `['auth']` — following the recommendation makes the two impossible to configure apart.

### 4. Install command

`stickle:install` currently prompts for `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` as free text, defaulting to `web` and `api` (`src/Commands/InstallCommand.php:179-200`). Both prompts are removed. They conflate transport plumbing with authorization, and their defaults are exactly what made the hole look configured.

One required prompt replaces them:

> Which middleware should guard the Stickle UI? — default `auth,can:viewStickle`

written to `STICKLE_ACCESS_MIDDLEWARE`. Transport middleware stays in the config file, where it belongs. On completion the command prints the `Gate::define('viewStickle', ...)` stub to paste into `AppServiceProvider`, because without it `can:viewStickle` denies everyone and the install appears broken.

### 5. Discoverability

A `php artisan about` section registered via `AboutCommand::add('Stickle', ...)` reports either `Access Guard: auth, can:viewStickle` or `Access Guard: NOT CONFIGURED — UI disabled`.

No per-boot log warning. That would be noise in every queue worker and scheduler tick for a condition already visible in `about` and on the setup page.

### 6. Upgrade path

This is a breaking change. Existing installations lose the UI until they set the new key. That includes installations that already set `STICKLE_WEB_MIDDLEWARE=auth`, because the package cannot distinguish a guard from plumbing and every install should opt in explicitly.

Required changes to ship alongside:

- A CHANGELOG entry and an `UPGRADE.md` section with the two-line fix.
- `workbench/` config sets `stickle.access.middleware` so the development server keeps working.
- `tests/TestCase.php::getEnvironmentSetUp` sets `stickle.access.middleware` so existing UI and API tests keep passing.
- `docs/guide/configuration.md` gains an `access` section; the `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` entries are rewritten to describe transport plumbing and to point at `STICKLE_ACCESS_MIDDLEWARE` for authorization.

## Testing

Pest, written failing first per test-driven development.

| Test | Asserts |
|---|---|
| `Access::middleware()` normalization | `null`, `''`, `[]` yield `[]`; `'auth,can:x'` yields `['auth','can:x']`; an array passes through unchanged |
| Guard unset, debug off | `GET /stickle` returns 404; `GET /stickle/api/segments` returns 404 |
| Guard unset, debug on | `GET /stickle` returns 503 and renders the setup view; `GET /stickle/api/segments` returns 503 JSON |
| Guard unset, both debug states | `POST /stickle/api/track` returns 200 — the catch-all ordering test |
| Guard set | UI and read routes are registered, and `gatherMiddleware()` on each contains the configured guard list |
| Channel authorizers | no Gate defined yields `false`; Gate allowing yields `true`; Gate denying yields `false` |

## Out of Scope

**Adding `mergeConfigFrom` to `CoreServiceProvider`.** It would change config defaults package-wide with real blast radius across existing tests and installs. The new key is designed to behave correctly without it: unpublished config yields `null`, which fails closed. This remains a known latent issue for other config-gated features and is tracked separately.

**Authenticating the ingest endpoint.** `POST /track` stays public, matching how the browser snippet uses it. A separate write-key or signed-request scheme is a plausible future addition and is not part of this change.

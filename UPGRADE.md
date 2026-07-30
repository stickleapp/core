# Upgrade Guide

## Unreleased

### `Identify`, `Group` and `RequestReceived` are removed

`StickleApp\Core\Events\Identify`, `StickleApp\Core\Events\Group` and
`StickleApp\Core\Events\RequestReceived`, along with their listeners
(`IdentifyListener`, `GroupListener`), are gone. No replacement is coming.

None of the three were ever dispatched by Stickle. `identify()` and
`group()` exist in analytics clients like Segment because those tools have
no knowledge of your domain and need the browser to tell them who a visitor
is and which account they belong to. Stickle already knows both from the
host application's own database: identity comes from the session
(`IngestController` falls back to `$request->user()`), and group membership
comes from a declared Eloquent relationship between your models. Adding
those verbs would create a second, browser-supplied source of truth — over
a deliberately public, unauthenticated endpoint — for facts the database
already holds. `RequestReceived` was unrelated dead scaffolding: no
dispatcher, no listener, no documentation.

If your application referenced any of these three classes — for example in
its own `$listen` map — remove that reference. Nothing dispatches them, so
removing the reference has no behavioral effect.

### Stickle now requires an access guard

Stickle's UI and read API used to be reachable by anyone who could reach the
URL. They are now closed until your application says who may open them.

**Required.** Add this to `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

Express it however your application already decides who is an
administrator — a column, a `spatie/laravel-permission` role, an existing
method. Until you do, every Stickle URL returns 403. Defining nothing is a
valid choice; it leaves Stickle closed.

`viewStickle` receives only `$user`, over both the HTTP routes and the
broadcast channels. Write it as `fn ($user) => ...`; a signature that expects
a model or record id will error on the HTTP path.

`POST /stickle/api/track` is unaffected and stays public, so browser
tracking keeps working.

**Stickle does not scope data by tenant.** Anyone this Gate allows sees
every tenant's users, events and sessions, not only their own. In a
multi-tenant application this ability should name administrators, not
customer-facing staff.

### The broadcast channels are now private

Stickle's events used to broadcast on public channels, which meant anyone
holding your application's Reverb/Pusher app key — public by design, since it
ships in your frontend bundle — could subscribe and receive every tracked
event, including the full model row on attribute-change events. The
`viewStickle` checks in `routes/channels.php` were never reached, because
Laravel only authorizes `private-`/`presence-` prefixed channels.

Every event in `src/Events/` now broadcasts on a `PrivateChannel`, and
Stickle's UI subscribes with `Echo.private()`. Subscriptions are authorized
against the same `viewStickle` ability that guards the HTTP routes.

**Required if you broadcast.** Your application must register the endpoint
that authorization happens at:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();
```

Laravel's `install:broadcasting` adds this, so most applications already have
it. Stickle does not register broadcast routes on your behalf. Without it
there is no `/broadcasting/auth` for a subscription to be authorized at, so
every subscription is refused; Stickle's UI falls back to polling the read
API and stays correct but no longer live.

If you subscribe to Stickle's channels from your own JavaScript, change
`Echo.channel('stickle.firehose')` to `Echo.private('stickle.firehose')`.
Laravel adds the `private-` prefix on the wire; the names in
`config/stickle.php` and the authorizer patterns stay un-prefixed, so no
configuration changes.

### `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` are no longer read

Both are now plain arrays in `config/stickle.php`:

```php
'middleware' => ['web'],
```

If you set either environment variable to something other than the default,
move the value into the published config as an array. If you set them to
`web` and `api` — what `stickle:install` used to write — nothing changes
behaviorally and the `.env` lines can be deleted.

These control transport middleware only. They cannot grant, weaken, or
remove access to Stickle; that is the Gate's job alone. Add `'auth'` here
if you would rather a signed-out visitor be redirected to your login page
than shown a bare 403.

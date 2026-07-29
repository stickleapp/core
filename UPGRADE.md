# Upgrade Guide

## Unreleased

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

`POST /stickle/api/track` is unaffected and stays public, so browser
tracking keeps working.

**This Gate does not cover the broadcast channels.** `routes/channels.php`
carries matching `viewStickle` checks, but they are never invoked: every
event in `src/Events/` broadcasts on a public `Channel`, not a
`PrivateChannel`, and Laravel only runs channel authorizers for
`private-`/`presence-` prefixed channels. See "Known limitation: broadcasting
is not authenticated" below.

**Stickle does not scope data by tenant.** Anyone this Gate allows sees
every tenant's users, events and sessions, not only their own. In a
multi-tenant application this ability should name administrators, not
customer-facing staff.

### Known limitation: broadcasting is not authenticated

Stickle's realtime broadcast stream is **not** authenticated, independent of
the Gate above. The events in `src/Events/` broadcast on public channels
(`new Channel`, not `PrivateChannel`), and the JS client subscribes with
`Echo.channel()`, not `Echo.private()`. Anyone holding your application's
Reverb/Pusher app key — public by design, since it ships in your frontend
bundle — can subscribe to Stickle's channels directly and receive every
tracked event in real time, including the full model row on attribute-change
events, without authenticating at all.

Converting the channels and client to private/presence channels is separate
work and not part of this release. Until it ships, your realistic options are
to disable broadcasting for Stickle or to accept this exposure knowingly.

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

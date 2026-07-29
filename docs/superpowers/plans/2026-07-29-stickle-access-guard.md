# Stickle Access Guard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close Stickle's UI, read API and broadcast channels behind a `viewStickle` Gate that denies until the host application defines it.

**Architecture:** Guarded route groups append the stock `can:viewStickle` middleware; the three broadcast channel authorizers call `Gate::allows('viewStickle', ...)`. Laravel already denies undefined abilities, so deny-by-default needs no code of ours. The guard is written by the route file, never read from config, so no configuration value can remove it. `POST /track` stays outside the guard.

**Tech Stack:** PHP 8.3+, Laravel 11+, Pest, Orchestra Testbench, PostgreSQL.

**Spec:** `docs/superpowers/specs/2026-07-29-stickle-access-guard-design.md`

## Global Constraints

- Adds **no new PHP classes**. Anything that feels like it needs `Support\Access` or `EnsureStickleAccess` is out of scope — see spec §1 and §2.
- The ability name is exactly `viewStickle` everywhere — route files, channel files, tests, docs.
- `can:viewStickle` is appended by the route file. It must never be read from, or be removable by, `stickle.routes.*.middleware`.
- `POST /stickle/api/track` must return 200 regardless of Gate state.
- Every task ends green: `composer test` passes before you commit.
- The pre-commit hook runs `rector --dry-run`, `pint --test`, `phpstan`, and the full Pest suite. A commit that fails any of them is rejected, so run `composer test` before committing rather than discovering it at commit time.
- Commit messages: imperative subject, body explaining *why*. End with:
  `Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>`

## File Structure

| File | Responsibility | Task |
|---|---|---|
| `tests/Pest.php` | Adds `withoutStickleGate()` helper | 1 |
| `tests/TestCase.php` | Defines an allowing `viewStickle` Gate so the existing suite stays green | 1 |
| `routes/api.php` | Guards read endpoints; leaves `/track` public | 1 |
| `tests/Feature/Access/ApiGuardTest.php` | API guard behavior | 1 |
| `routes/web.php` | Guards the UI group | 2 |
| `tests/Feature/Access/WebGuardTest.php` | UI guard behavior | 2 |
| `routes/channels.php` | Gate-checks the three channel authorizers | 3 |
| `tests/Feature/Access/ChannelGuardTest.php` | Channel authorizer behavior | 3 |
| `workbench/app/Providers/WorkbenchServiceProvider.php` | Defines the Gate for the dev server | 3 |
| `config/stickle.php` | Transport middleware becomes literal arrays, drops `env()` | 4 |
| `tests/Feature/Access/TransportMiddlewareTest.php` | Guard survives config; `auth` still composable | 4 |
| `src/Commands/InstallCommand.php` | Stops prompting for the two middleware keys | 5 |
| `README.md`, `UPGRADE.md`, `CHANGELOG.md`, `docs/guide/configuration.md` | Operator-facing documentation | 6 |

---

### Task 1: Guard the read API, keep ingest public

**Files:**
- Modify: `tests/Pest.php`
- Modify: `tests/TestCase.php:40-60` (`getEnvironmentSetUp`)
- Modify: `routes/api.php:18-50`
- Create: `tests/Feature/Access/ApiGuardTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: `withoutStickleGate(): void` — a Pest helper that rebinds a fresh, empty Gate so a test can assert the "never defined" state. Tasks 2, 3 and 4 all use it.

- [ ] **Step 1: Add the `withoutStickleGate()` helper**

`tests/TestCase.php` will define an allowing Gate for the whole suite, so a test that needs the undefined state must replace the Gate instance. Laravel's Gate has no method to forget an ability — verified, it exposes only `define()` and `abilities()` — so the helper rebinds a fresh one.

Append to `tests/Pest.php`:

```php
use Illuminate\Auth\Access\Gate as GateInstance;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

/**
 * Replace the container's Gate with an empty one, so no ability is defined.
 *
 * Laravel's Gate can define abilities but not forget them, and TestCase
 * defines viewStickle for the rest of the suite. This is how a test asserts
 * the state a fresh install is in.
 */
function withoutStickleGate(): void
{
    app()->singleton(
        GateContract::class,
        fn ($app): GateInstance => new GateInstance($app, fn () => $app['auth']->user())
    );
}
```

- [ ] **Step 2: Define an allowing Gate for the existing suite**

Nine existing test files under `tests/Feature/Api/` call these endpoints and would start returning 403. Add to the end of `getEnvironmentSetUp()` in `tests/TestCase.php`:

```php
// The package denies until the application defines this. The suite exercises
// the routes themselves, so it stands in as that application. Tests that need
// the unconfigured state call withoutStickleGate().
Gate::define('viewStickle', fn ($user = null): bool => true);
```

Add the import: `use Illuminate\Support\Facades\Gate;`

The `$user = null` default is deliberate — it is what lets the ability be evaluated for an unauthenticated request. Without a default or a nullable type hint, Laravel skips the callback for guests and denies.

- [ ] **Step 3: Write the failing tests**

Create `tests/Feature/Access/ApiGuardTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('denies a read endpoint when no gate is defined', function (): void {

    withoutStickleGate();

    $this->getJson('/stickle/api/segments')->assertForbidden();
});

it('denies a read endpoint when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->getJson('/stickle/api/segments')->assertForbidden();
});

it('allows a read endpoint when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $this->getJson('/stickle/api/segments')->assertOk();
});

it('keeps ingest public when no gate is defined', function (): void {

    withoutStickleGate();

    $this->postJson('/stickle/api/track', [])->assertStatus(422);
});

it('keeps ingest public when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->postJson('/stickle/api/track', [])->assertStatus(422);
});

it('keeps ingest reachable when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $this->postJson('/stickle/api/track', [])->assertStatus(422);
});
```

The ingest tests assert 422, not 200: an empty body fails `IngestController`'s validation. That is the point — a 422 proves the request reached the controller rather than being stopped by the guard, which is exactly what these two tests exist to show. A 403 here would mean the guard leaked onto `/track`.

- [ ] **Step 4: Run the tests and watch them fail**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/ApiGuardTest.php
```

Expected: the two `denies` tests FAIL with 200 instead of 403, because nothing guards the route yet. The `allows` and both ingest tests should already PASS. If a `denies` test passes at this point, the test is wrong — check that `withoutStickleGate()` is being applied.

- [ ] **Step 5: Guard the read endpoints**

In `routes/api.php`, wrap every route except `/track` in a nested group. Replace lines 18-50 with:

```php
Route::middleware(config('stickle.routes.api.middleware', ['api']))
    ->prefix(config('stickle.routes.api.prefix', 'stickle/api'))->group(function (): void {

        /**
         * Public. The browser tracking snippet posts from unauthenticated
         * visitors, so this must stay outside the guard below.
         */
        Route::post('/track', [IngestController::class, 'store'])
            ->name('stickle/track');

        /**
         * Everything else reads customer data. can:viewStickle is written
         * here rather than read from config, so no configuration value can
         * remove it. Laravel denies an ability that was never defined, so an
         * application that has not defined viewStickle is closed.
         */
        Route::middleware('can:viewStickle')->group(function (): void {

            Route::get('/requests', [RequestsController::class, 'index'])
                ->name('stickle::api.requests');

            Route::get('/segment-statistics', [SegmentStatisticsController::class, 'index'])
                ->name('segment-statistics');

            Route::get('/segment-models', [SegmentModelsController::class, 'index'])
                ->name('segment-models');

            Route::get('/segments', [SegmentsController::class, 'index'])
                ->name('segments');

            Route::get('/models', [ModelsController::class, 'index'])
                ->name('models');

            Route::get('/models-statistics', [ModelsStatisticsController::class, 'index'])
                ->name('models-statistics');

            Route::get('/model-relationship', [ModelRelationshipController::class, 'index'])
                ->name('models-relationship');

            Route::get('/model-relationship-statistics', [ModelRelationshipStatisticsController::class, 'index'])
                ->name('model-relationship-statistics');

            Route::get('/model-attribute-audit', [ModelAttributeAuditController::class, 'index'])
                ->name('model-attribute-audit');
        });
    });
```

- [ ] **Step 6: Run the tests and watch them pass**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/ApiGuardTest.php
```

Expected: 5 passed.

- [ ] **Step 7: Run the whole suite**

```bash
composer test
```

Expected: all green. If `tests/Feature/Api/*` fail with 403, Step 2 did not take effect — confirm `Gate::define` is inside `getEnvironmentSetUp()` and that `Illuminate\Support\Facades\Gate` is imported.

- [ ] **Step 8: Commit**

```bash
git add tests/Pest.php tests/TestCase.php routes/api.php tests/Feature/Access/ApiGuardTest.php
git commit -m "$(cat <<'EOF'
Close the Stickle read API behind a viewStickle gate

Every read endpoint served customer data under transport middleware
only. They now sit in a nested group carrying can:viewStickle, written
by the route file so no config value can remove it. Laravel denies an
ability that was never defined, so an application that has not defined
viewStickle is closed without any deny-by-default code of our own.

POST /track stays outside the group. The browser snippet posts from
unauthenticated visitors, and the tests assert it returns 422 rather
than 403 under every gate state -- a validation failure proves the
request reached the controller.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 2: Guard the UI routes

**Files:**
- Modify: `routes/web.php:12-14`
- Create: `tests/Feature/Access/WebGuardTest.php`

**Interfaces:**
- Consumes: `withoutStickleGate()` from Task 1.
- Produces: nothing.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Access/WebGuardTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('denies the UI when no gate is defined', function (): void {

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('denies the UI when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $this->get('/stickle/live')->assertForbidden();
});

it('allows the UI when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $this->get('/stickle/live')->assertOk();
});
```

- [ ] **Step 2: Run the tests and watch them fail**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/WebGuardTest.php
```

Expected: the two `denies` tests FAIL with 200 instead of 403.

If the `allows` test fails for an unrelated reason — the live page resolves `Workbench\App\Models\User` and renders the layout — fix that first and confirm it passes before continuing. A guard test is only meaningful once the unguarded route is known to work.

- [ ] **Step 3: Guard the group**

In `routes/web.php`, replace line 12:

```php
Route::middleware(config('stickle.routes.web.middleware', ['web']))
```

with:

```php
/**
 * can:viewStickle is written here rather than read from config, so no
 * configuration value can remove it. The configured list is transport
 * plumbing only -- session, cookies, and optionally auth for a login
 * redirect instead of a bare 403.
 */
Route::middleware([
    ...Arr::wrap(config('stickle.routes.web.middleware', ['web'])),
    'can:viewStickle',
])
```

Add the import at the top of the file: `use Illuminate\Support\Arr;`

`Arr::wrap` is needed because the array literal is spread — a bare string from a published config would be a fatal spread error. It returns `[]` for `null`, wraps a string, and passes an array through.

- [ ] **Step 4: Run the tests and watch them pass**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/WebGuardTest.php
```

Expected: 3 passed.

- [ ] **Step 5: Run the whole suite**

```bash
composer test
```

Expected: all green.

- [ ] **Step 6: Commit**

```bash
git add routes/web.php tests/Feature/Access/WebGuardTest.php
git commit -m "$(cat <<'EOF'
Close the Stickle UI behind a viewStickle gate

The UI group carried config('stickle.routes.web.middleware') alone,
which defaults to 'web' -- session and cookie plumbing, not
authorization -- and falls through to no middleware at all in an
application that has not published the config. can:viewStickle is now
appended by the route file, so the configured list controls plumbing
and cannot weaken the guard.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 3: Gate the broadcast channels

**Files:**
- Modify: `routes/channels.php` (all 15 lines)
- Create: `tests/Feature/Access/ChannelGuardTest.php`
- Modify: `workbench/app/Providers/WorkbenchServiceProvider.php` (`boot()`)

**Interfaces:**
- Consumes: `withoutStickleGate()` from Task 1.
- Produces: nothing.

- [ ] **Step 1: Write the failing tests**

`Broadcaster::getChannels()` is public and returns the registered callbacks keyed by channel pattern, so the authorizers can be invoked directly.

Create `tests/Feature/Access/ChannelGuardTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

function stickleChannelCallback(string $configKey): callable
{
    $channels = Broadcast::driver()->getChannels();

    $pattern = config($configKey);

    expect($channels)->toHaveKey($pattern);

    return $channels[$pattern];
}

it('denies the firehose channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeFalse();
});

it('denies the firehose channel when the gate denies', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => false);

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeFalse();
});

it('allows the firehose channel when the gate allows', function (): void {

    Gate::define('viewStickle', fn ($user = null): bool => true);

    $callback = stickleChannelCallback('stickle.broadcasting.channels.firehose');

    expect($callback(null))->toBeTrue();
});

it('denies the object channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.object');

    expect($callback(null, 'user', '1'))->toBeFalse();
});

it('denies the class channel when no gate is defined', function (): void {

    withoutStickleGate();

    $callback = stickleChannelCallback('stickle.broadcasting.channels.class');

    expect($callback(null, 'user'))->toBeFalse();
});
```

- [ ] **Step 2: Run the tests and watch them fail**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/ChannelGuardTest.php
```

Expected: the four `denies` tests FAIL — the callbacks currently `return true` unconditionally.

If instead every test errors on `getChannels()`, the channels are registered against a different broadcaster instance than `Broadcast::driver()` resolves. Resolve that before continuing: the test is only meaningful if it is reading the callbacks the package actually registered.

- [ ] **Step 3: Gate the authorizers**

Replace the whole of `routes/channels.php`:

```php
<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/**
 * The same viewStickle ability that guards the HTTP routes, so the two
 * transports cannot drift apart. Laravel denies an ability that was never
 * defined, so an application that has not defined it rejects subscriptions.
 *
 * Route parameters are forwarded as the Gate's context, which lets an
 * application scope per record without being required to: PHP ignores extra
 * arguments passed to a closure, so fn ($user) => $user->is_admin still works.
 */
Broadcast::channel(config('stickle.broadcasting.channels.firehose'), function ($user): bool {
    return Gate::forUser($user)->allows('viewStickle');
});

Broadcast::channel(config('stickle.broadcasting.channels.object'), function ($user, $model, $id): bool {
    return Gate::forUser($user)->allows('viewStickle', [$model, $id]);
});

Broadcast::channel(config('stickle.broadcasting.channels.class'), function ($user, $model): bool {
    return Gate::forUser($user)->allows('viewStickle', [$model]);
});
```

`Gate::forUser($user)` rather than `Gate::allows(...)`: the channel callback receives the subscribing user as an argument, and is not necessarily running inside a request whose authenticated user the Gate would otherwise resolve.

- [ ] **Step 4: Run the tests and watch them pass**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/ChannelGuardTest.php
```

Expected: 5 passed.

- [ ] **Step 5: Keep the development server working**

The workbench is an application, so it must define the Gate like any other. Add to `boot()` in `workbench/app/Providers/WorkbenchServiceProvider.php`:

```php
// The workbench stands in as the host application, which is what has to
// decide who may open Stickle. Everything is permitted here because the
// dev server has no notion of an administrator.
Gate::define('viewStickle', fn ($user = null): bool => true);
```

Add the import: `use Illuminate\Support\Facades\Gate;`

- [ ] **Step 6: Run the whole suite**

```bash
composer test
```

Expected: all green.

- [ ] **Step 7: Commit**

```bash
git add routes/channels.php tests/Feature/Access/ChannelGuardTest.php workbench/app/Providers/WorkbenchServiceProvider.php
git commit -m "$(cat <<'EOF'
Gate the Stickle broadcast channels

All three authorizers returned true unconditionally, with the intended
is_admin check sitting commented out beside them, so the live data
stream was readable by anyone who could open a socket. They now check
the same viewStickle ability that guards the HTTP routes, which is what
stops the two transports drifting apart.

Route parameters are forwarded as the Gate's context so an application
can scope per record if it wants to. PHP ignores extra arguments passed
to a closure, so a plain fn ($user) => $user->is_admin keeps working.

The workbench defines the ability so the development server still runs.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 4: Make the transport keys plain arrays

**Files:**
- Modify: `config/stickle.php` (the `routes` block, around lines 205-213)
- Create: `tests/Feature/Access/TransportMiddlewareTest.php`

**Interfaces:**
- Consumes: `withoutStickleGate()` from Task 1.
- Produces: nothing.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Access/TransportMiddlewareTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    putenv('STICKLE_WEB_MIDDLEWARE=auth');
    putenv('STICKLE_API_MIDDLEWARE=auth');
});

afterEach(function (): void {
    putenv('STICKLE_WEB_MIDDLEWARE');
    putenv('STICKLE_API_MIDDLEWARE');
});

it('cannot be opened by emptying the configured middleware', function (): void {

    config()->set('stickle.routes.web.middleware', []);

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('cannot be opened by unsetting the configured middleware', function (): void {

    config()->set('stickle.routes.web.middleware', null);

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('still applies the configured transport middleware', function (): void {

    // Without a login route the auth middleware's redirect would throw
    // RouteNotFoundException rather than producing an assertable response.
    Route::get('/login', fn (): string => 'login')->name('login');

    config()->set('stickle.routes.web.middleware', ['web', 'auth']);

    Gate::define('viewStickle', fn ($user = null): bool => true);

    // auth runs ahead of the guard, so an unauthenticated visitor is sent to
    // log in rather than shown a bare 403. This is the only reason these
    // config keys were kept, so it is worth asserting rather than assuming.
    $this->get('/stickle/live')->assertRedirect('/login');
});

it('does not read the removed environment variables', function (): void {

    $config = require __DIR__.'/../../../config/stickle.php';

    expect($config['routes']['web']['middleware'])->toBe(['web']);
    expect($config['routes']['api']['middleware'])->toBe(['api']);
});
```

The last test loads the config file directly with both environment variables set to a value that is not the default, which the `beforeEach` above arranges. If the file still calls `env()`, the returned middleware is `'auth'` and the assertion fails. This is what proves the defect is fixed at the source rather than worked around.

- [ ] **Step 2: Run the tests and watch them fail**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/TransportMiddlewareTest.php
```

Expected: the two "cannot be opened" tests and the transport-middleware test should already PASS (Task 2 put the guard outside config and spread the configured list). The `env()` test FAILS, because the config file still reads the variables.

If a "cannot be opened" test fails, the guard is being read from config somewhere — stop and fix that, it is the core property of this whole change.

If the `env()` test passes *before* you make the change, `putenv()` is not reaching Laravel's env repository — set `$_SERVER['STICKLE_WEB_MIDDLEWARE']` instead and re-run. A test that cannot fail proves nothing.

- [ ] **Step 3: Drop `env()` from the two middleware keys**

In `config/stickle.php`, replace the `routes` block:

```php
    'routes' => [
        'api' => [
            'prefix' => env('STICKLE_API_PREFIX', 'stickle/api'),

            /*
            | Transport plumbing only. Authorization is the viewStickle Gate,
            | which this list cannot grant, weaken, or remove.
            |
            | An array, not env(): env() can only return a scalar, so
            | STICKLE_API_MIDDLEWARE="auth,throttle:60" used to register one
            | middleware with a comma in its name and fail silently.
            */
            'middleware' => ['api'],
        ],
        'web' => [
            'prefix' => env('STICKLE_WEB_PREFIX', 'stickle'),

            /*
            | Transport plumbing only -- see the note above. Add 'auth' here
            | if you would rather a signed-out visitor were redirected to your
            | login page than shown a bare 403.
            */
            'middleware' => ['web'],
        ],
    ],
```

- [ ] **Step 4: Run the tests and watch them pass**

```bash
php -d memory_limit=1G vendor/bin/pest tests/Feature/Access/TransportMiddlewareTest.php
```

Expected: 4 passed.

- [ ] **Step 5: Run the whole suite**

```bash
composer test
```

Expected: all green.

- [ ] **Step 6: Commit**

```bash
git add config/stickle.php tests/Feature/Access/TransportMiddlewareTest.php
git commit -m "$(cat <<'EOF'
Make the Stickle transport middleware plain arrays

env() can only return a scalar, so STICKLE_API_MIDDLEWARE="auth,can:admin"
registered a single middleware with a comma in its name and failed
silently -- an operator's attempt to secure the package did nothing. The
keys are now literal arrays in the published config, which removes the
string case rather than parsing around it. An application that wants
'auth' writes ['web', 'auth'].

The keys survive only as transport plumbing. Tests assert that emptying
or unsetting them still leaves the UI closed, since the guard is written
by the route file.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 5: Stop the installer writing the middleware variables

**Files:**
- Modify: `src/Commands/InstallCommand.php:59,61` (the label map) and `:179-201` (the two prompts)

**Interfaces:**
- Consumes: nothing.
- Produces: nothing.

**No test changes.** `tests/Unit/Commands/InstallCommandTest.php` exercises only `createInitialPartitions()`, invoked through reflection — it never drives the interactive form, so removing two prompts cannot break it. There is also no test added here: `form()` is interactive and the prompts have no return value to assert against. The guard for this task is the repo-wide grep in the Verification section, which fails if either variable name survives anywhere in `src/`. Say so plainly in review rather than implying this task is covered by tests.

- [ ] **Step 1: Remove the two prompts**

In `src/Commands/InstallCommand.php`, delete these two blocks from the `form()` chain:

```php
            ->note('Stickle will apply middleware you require to the web routes.')
            ->text(
                label: 'What middleware would you like to apply to the web routes?',
                default: 'web',
                validate: ['STICKLE_WEB_MIDDLEWARE' => 'string'],
                name: 'STICKLE_WEB_MIDDLEWARE'
            )
```

```php
            ->note('Stickle will apply middleware you require to the API routes.')
            ->text(
                label: 'What middleware would you like to apply to the API routes?',
                default: 'api',
                validate: ['STICKLE_API_MIDDLEWARE' => 'string'],
                name: 'STICKLE_API_MIDDLEWARE'
            )
```

Delete these two lines from the label map at lines 59 and 61:

```php
            'STICKLE_WEB_MIDDLEWARE' => 'Web Middleware',
```
```php
            'STICKLE_API_MIDDLEWARE' => 'API Middleware',
```

Leave `STICKLE_WEB_PREFIX` and `STICKLE_API_PREFIX` alone — prefixes are still environment-configurable.

- [ ] **Step 2: Tell the operator what to do instead**

The installer's last act should be the one instruction that matters. Find where the command reports completion and add:

```php
note('Stickle is closed until you define who may open it.');
note('Add this to AppServiceProvider::boot():');
note("    Gate::define('viewStickle', fn (\$user) => \$user->is_admin);");
```

Without this the install completes and every Stickle URL returns 403, which reads as a broken install rather than an unfinished one.

- [ ] **Step 3: Confirm the variables are gone**

```bash
grep -rn "STICKLE_WEB_MIDDLEWARE\|STICKLE_API_MIDDLEWARE" src/
```

Expected: no output. Any hit is a place still reading or writing a variable that is no longer honoured.

- [ ] **Step 4: Run the whole suite**

```bash
composer test
```

Expected: all green, including `tests/Unit/Commands/InstallCommandTest.php`.

- [ ] **Step 5: Commit**

```bash
git add src/Commands/InstallCommand.php
git commit -m "$(cat <<'EOF'
Stop the installer prompting for route middleware

The two prompts asked which middleware should guard the web and API
routes, defaulting to 'web' and 'api'. That framing is where transport
plumbing got conflated with authorization, and the defaults are what
made the hole look configured. They also wrote scalars, producing the
STICKLE_API_MIDDLEWARE="a,b" case that silently fails.

The installer now closes by printing the Gate definition instead, since
an install that completes with every Stickle URL returning 403 reads as
broken rather than unfinished.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 6: Document the guard and the upgrade

**Files:**
- Create: `UPGRADE.md`
- Modify: `README.md`
- Modify: `CHANGELOG.md`
- Modify: `docs/guide/configuration.md`

**Interfaces:**
- Consumes: nothing.
- Produces: nothing.

- [ ] **Step 1: Read what is there**

```bash
head -60 CHANGELOG.md
grep -n "MIDDLEWARE" docs/guide/configuration.md
grep -n "install\|Installation" README.md | head
```

You are matching existing structure, not inventing one. Note the CHANGELOG's format (Keep a Changelog headings, or plain version headings) and where the README documents installation.

- [ ] **Step 2: Create `UPGRADE.md`**

```markdown
# Upgrade Guide

## Unreleased

### Stickle now requires an access guard

Stickle's UI, read API and broadcast channels used to be reachable by
anyone who could reach the URL. They are now closed until your application
says who may open them.

**Required.** Add this to `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

Express it however your application already decides who is an
administrator — a column, a `spatie/laravel-permission` role, an existing
method. Until you do, every Stickle URL returns 403 and every broadcast
subscription is rejected. Defining nothing is a valid choice; it leaves
Stickle closed.

`POST /stickle/api/track` is unaffected and stays public, so browser
tracking keeps working.

**Stickle does not scope data by tenant.** Anyone this Gate allows sees
every tenant's users, events and sessions, not only their own. In a
multi-tenant application this ability should name administrators, not
customer-facing staff.

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
```

- [ ] **Step 3: Add the Gate to the README**

In the installation section, after the config publishing step, add:

```markdown
### Grant access

Stickle is closed until you say who may open it. Add to `AppServiceProvider::boot()`:

```php
Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

Every Stickle URL returns 403 until this is defined, and every broadcast
subscription is rejected. Stickle does not scope data by tenant, so anyone
allowed here sees every tenant's data.
```

- [ ] **Step 4: Add the CHANGELOG entry**

Matching the format found in Step 1, under an Unreleased heading:

```markdown
### Changed
- **BREAKING** Stickle's UI, read API and broadcast channels now require a `viewStickle` Gate. See `UPGRADE.md`.
- **BREAKING** `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` are no longer read; the values are arrays in `config/stickle.php`.
- `stickle:install` no longer prompts for route middleware and prints the Gate definition on completion.
```

- [ ] **Step 5: Rewrite the configuration guide entries**

In `docs/guide/configuration.md`, at the lines Step 1 found, replace the `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` documentation with an `access` section that states: the Gate is the only thing granting access; the two config arrays are transport plumbing; adding `auth` gets a login redirect instead of a 403; and Stickle is not tenant-scoped.

- [ ] **Step 6: Verify the docs build**

```bash
npm run docs:build
```

Expected: builds without error. `docs/superpowers/**` is excluded from the site via `srcExclude`, but `docs/guide/**` is not — a broken link or malformed frontmatter there fails the build.

- [ ] **Step 7: Run the whole suite**

```bash
composer test
```

Expected: all green.

- [ ] **Step 8: Commit**

```bash
git add UPGRADE.md README.md CHANGELOG.md docs/guide/configuration.md
git commit -m "$(cat <<'EOF'
Document the Stickle access guard and its upgrade

Both changes in this series are breaking: installations get 403 until
they define viewStickle, and two environment variables stop being read.
A silently ignored environment variable is the failure mode this series
exists to remove, so the upgrade note states it plainly rather than
leaving it to be discovered.

Also records that Stickle has no tenant scoping, so the ability should
name administrators rather than customer-facing staff. That is easy to
get wrong precisely when the Gate looks like it solved authorization.

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Verification

After Task 6, confirm the whole change end to end:

- [ ] `composer verify` passes (refactor, format, test, analyse, npm build)
- [ ] `grep -rn "STICKLE_WEB_MIDDLEWARE\|STICKLE_API_MIDDLEWARE" src/ config/ routes/` returns nothing outside comments
- [ ] `grep -rn "viewStickle" routes/` shows the guard in `api.php`, `web.php` and `channels.php`
- [ ] No new file exists under `src/Support/` or `src/Middleware/` — this change adds no PHP classes

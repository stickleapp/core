---
outline: deep
---

# Advanced Configuration

You can fine-tune your Stickle installation using the configuration options below. All configuration options are defined in `config/stickle.php`.

To override these settings, add the following items to your `env` file or system environmental settings.

## Schedule

Controls how frequently (in minutes) various background tasks are executed.

### `STICKLE_FREQUENCY_EXPORT_SEGMENTS`

How often (in minutes) to export your segments of `StickleEntity` models, storing a list of the models that are included in each segment.

_Default: 360_

### `STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES`

How often (in minutes) to record point-in-time snapshots of tracked model attributes.

NOTE: When a model is updated, via Eloquent, it is updated automatically.

_Default: 360_

### `STICKLE_FREQUENCY_RECORD_MODEL_RELATIONSHIP_STATISTICS`

How often (in minutes) to record aggregate statistics for model relationships including count, sum, min, max, and average values.

_Default: 360_

### `STICKLE_FREQUENCY_RECORD_SEGMENT_STATISTICS`

How often (in minutes) to record segment statistics -- that is the aggregates of the model attributes returned by the `stickleTrackedAttributes()` method -- at the Segment-level.

_Default: 360_

## Database

Database connection and table configuration settings.

### `STICKLE_DATABASE_SCHEMA`

The database schema to use. Must be a PostgreSQL-based connection.

_Default: 'public'_

### `STICKLE_DATABASE_TABLE_PREFIX`

Prefix for all Stickle database tables.

_Default: 'stc\_'_

### `STICKLE_DATABASE_ENABLE_PARTITIONS`

Whether to enable partitioning for time-series data tables.

_Default: true_

### Partitions

Controls partitioning settings for time-series data tables. Each table (events, requests, sessions) can be configured independently.

##### `STICKLE_DATABASE_PARTITIONS_EVENTS_INTERVAL`

Partition interval for the events table.

_Default: 'week'_

##### `STICKLE_DATABASE_PARTITIONS_EVENTS_EXTENSION`

How far ahead to create partitions for the events table.

_Default: '1 week'_

##### `STICKLE_DATABASE_PARTITIONS_EVENTS_RETENTION`

How long to retain data in the events table.

_Default: '1 years'_

##### `STICKLE_DATABASE_PARTITIONS_REQUESTS_INTERVAL`

Partition interval for the requests table.

_Default: 'week'_

##### `STICKLE_DATABASE_PARTITIONS_REQUESTS_EXTENSION`

How far ahead to create partitions for the requests table.

_Default: '1 week'_

##### `STICKLE_DATABASE_PARTITIONS_REQUESTS_RETENTION`

How long to retain data in the requests table.

_Default: '1 years'_

##### `STICKLE_DATABASE_PARTITIONS_SESSIONS_INTERVAL`

Partition interval for the sessions table.

_Default: 'week'_

##### `STICKLE_DATABASE_PARTITIONS_SESSIONS_EXTENSION`

How far ahead to create partitions for the sessions table.

_Default: '1 week'_

##### `STICKLE_DATABASE_PARTITIONS_SESSIONS_RETENTION`

How long to retain data in the sessions table.

_Default: '1 years'_

## Namespaces

Define where certain classes are located in your Laravel project.

### `STICKLE_NAMESPACES_SEGMENTS`

Namespace where segment classes are stored.

_Default: 'App\Segments'_

### `STICKLE_NAMESPACES_LISTENERS`

Namespace where event listeners are stored.

_Default: 'App\Listeners'_

### `STICKLE_NAMESPACES_MODELS`

Namespace where trackable model classes are stored.

_Default: 'App\Models'_

## Filesystem

Storage configuration for exports and file operations.

### disks

Filesystem disk configurations for different storage needs.

#### `STICKLE_FILESYSTEM_DISK_EXPORTS`

Laravel filesystem disk to use for storing segment exports and related files.

Only used when [`STICKLE_USE_CSV_EXPORTS`](#stickle_use_csv_exports) is `true`. The
default segment sync writes no files.

_Default: 'local'_

## Segments

### `STICKLE_USE_CSV_EXPORTS`

Selects how a segment's membership is reconciled.

_Default: `false`_

#### `false` (default) — in-database sync

`SyncSegmentAction` reconciles membership in a single SQL statement. Both ends of the
operation are the same database, so no file, no external binary and no shared disk are
involved, and a failure raises a PDO exception rather than passing silently.

#### `true` — legacy CSV round trip

`ExportSegmentAction` writes the segment query to a CSV, hands the filename to a second
queued job (`ImportSegmentJob`), which loads it back with a `psql \copy`. Both paths are
equivalent in result and both are idempotent, so enable this only if you specifically
need it. It carries real requirements and known hazards:

- **Requires the `psql` binary** on every worker that runs `ImportSegmentJob` — it is
  shelled out to via `exec()`. If `psql` is missing the COPY fails, its exit code is
  discarded, and reconciliation proceeds against an **empty temp table**, deleting the
  segment's entire membership and firing a spurious `EXIT` for every member. The job
  still reports success.
- **Requires a shared exports disk.** `ExportSegmentJob` and `ImportSegmentJob` are
  separate queued jobs, so on any multi-instance or containerised host (Cloud Run, ECS,
  Kubernetes, or simply more than one queue worker) they will not share a local
  filesystem, and the import fails with `File missing`. `local` is only safe when a
  single machine runs both jobs; anything else needs `s3`, `gcs`, or similar.
- **Leaks the database password.** The import interpolates it into a shell string as
  `PGPASSWORD=…`, where it is visible in the process list to any local user.
- **Is broken by `$appends`.** Each CSV row is built from the model's `toArray()`, which
  includes appended accessors — so any model declaring `protected $appends` emits extra
  columns and the COPY fails with `extra data after last expected column`. The default
  SQL path never hydrates models and is unaffected.

## Routes

Route configuration for Stickle's web and API endpoints.

### api

Configuration for API route handling.

#### `STICKLE_API_PREFIX`

URL prefix for API routes.

_Default: 'stickle/api'_

`routes.api.middleware` is not an environment variable; see [Access](#access)
below.

### web

Configuration for web route handling.

#### `STICKLE_WEB_PREFIX`

URL prefix for web routes.

_Default: 'stickle'_

`routes.web.middleware` is not an environment variable; see [Access](#access)
below.

### Access

Stickle's UI and read API are authorized by a single Gate ability,
`viewStickle`, which your application defines in
`AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

`viewStickle` receives only `$user`, over both the HTTP routes and the
broadcast channels in `routes/channels.php` -- there is no per-record
context to inspect.

The Gate is the only thing granting access to the UI and read API.
`routes.api.middleware` and `routes.web.middleware` (above) are transport
plumbing -- session, cookies, throttling -- not authorization. They are plain
arrays in `config/stickle.php` and are no longer read from
`STICKLE_API_MIDDLEWARE` or `STICKLE_WEB_MIDDLEWARE`; neither one can grant,
weaken, or remove access to Stickle. If `viewStickle` is never defined,
Laravel denies the ability by default and every Stickle URL is closed.

`viewStickle` also authorizes broadcast subscriptions; see
"Authorizing subscriptions" below.

Add `'auth'` to `routes.web.middleware` if you would rather a signed-out
visitor be redirected to your login page than shown a bare 403.

**Stickle does not scope data by tenant.** Anyone the Gate allows sees
every tenant's users, events and sessions, not only their own. In a
multi-tenant application `viewStickle` should name administrators, not
customer-facing staff.

## Broadcasting

Configuration for real-time event broadcasting using Websockets.

### Authorizing subscriptions

Every event in `src/Events/` broadcasts on a `PrivateChannel`, so a client
subscribing to a Stickle channel is authorized before it receives anything.
Laravel routes the subscription through `/broadcasting/auth`, which runs the
`viewStickle` checks in the package's `routes/channels.php` -- the same
ability that guards the UI and the read API. An application that has not
defined `viewStickle` refuses every subscription, exactly as it refuses every
Stickle URL.

**Your application must register that endpoint.** Stickle does not add
broadcast routes on your behalf:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();
```

Laravel's `install:broadcasting` writes this for you, so most applications
already have it. Without it there is no `/broadcasting/auth`, nowhere for a
subscription to be authorized, and so no subscription succeeds -- Stickle's
UI falls back to polling the read API and stays correct but no longer live.

The channel names below are the un-prefixed names used by the events, the
authorizer patterns and the config. Laravel adds `private-` on the wire and
strips it again before authorizing, so the prefix never appears in
configuration. If you subscribe from your own JavaScript, use
`Echo.private()`.

### Broadcast Channels

Channel name configurations for different types of broadcasts.

#### `STICKLE_BROADCASTING_CHANNEL_FIREHOSE`

Channel name for broadcasting all Stickle events.

_Default: 'stickle.firehose'_

#### `STICKLE_BROADCASTING_CHANNEL_OBJECT`

Channel name pattern for object-specific events. Uses sprintf formatting with model type and ID.

_Default: 'stickle.object.%s.%s'_

## Tracking

Settings that control tracking behavior for both server-side and client-side events.

### Server

Server-side tracking configuration options.

#### `STICKLE_TRACK_SERVER_MODEL_ATTRIBUTES`

Whether to observe model attribute changes. When disabled, the model attribute observer is not registered and no `ModelAttributeChanged` events will be dispatched on model save.

_Default: true_

#### `STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE`

Whether to automatically load server-side tracking middleware.

_Default: true_

#### `STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS`

Controls whether the authentication event listener is registered. When enabled, Stickle will track the authentication events listed in the configuration.

_Default: true_

#### `STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED`

Comma-separated list of authentication events to track when authentication event tracking is enabled.

_Default:_

-   Authenticated
-   CurrentDeviceLogout
-   Login
-   Logout
-   OtherDeviceLogout
-   PasswordReset
-   Registered
-   Validated
-   Verified

**Example:** To track only login and logout events:

```
STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED=Login,Logout
```

### Client

Client-side tracking configuration options.

#### `STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE`

Whether to automatically load client-side tracking middleware.

_Default: true_

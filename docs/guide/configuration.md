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

_Default: 'local'_

## Routes

Route configuration for Stickle's web and API endpoints.

### api

Configuration for API route handling.

#### `STICKLE_API_PREFIX`

URL prefix for API routes.

_Default: 'stickle/api'_

#### `STICKLE_API_MIDDLEWARE`

Middleware to apply to API routes.

_Default: ['api']_

### web

Configuration for web route handling.

#### `STICKLE_WEB_PREFIX`

URL prefix for web routes.

_Default: 'stickle'_

#### `STICKLE_WEB_MIDDLEWARE`

Middleware to apply to web routes.

_Default: ['web']_

## Broadcasting

Configuration for real-time event broadcasting using Websockets.

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

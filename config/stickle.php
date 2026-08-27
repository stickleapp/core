<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Scheduling Frequencies
    |--------------------------------------------------------------------------
    |
    | How stale (in minutes) a record may get before the task refreshes it.
    |
    | These are not cron cadences. Each task is scheduled to tick every five
    | minutes and compares a last-recorded timestamp against the value below,
    | so this is the effective refresh rate. Lower it and records refresh
    | sooner; the schedule itself does not change.
    |
    | - Export Segments. Store the objects (users, groups, etc) that are part of each segment
    | - Record Segment Statistics. Store the number of users in each segment
    | - Record Entity Statistics. Store the number of users in each group
    | - Rollup Events. Aggregate the events into the event statistics table
    | - Rollup Page Views. Aggregate the page views into the page view statistics table
    | - Rollup Sessions. Aggregate the sessions into the session statistics table
    */
    'schedule' => [
        'exportSegments' => env('STICKLE_FREQUENCY_EXPORT_SEGMENTS', 360),
        'recordModelAttributes' => env(
            'STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES',
            360,
        ),
        'recordModelRelationshipStatistics' => env(
            'STICKLE_FREQUENCY_RECORD_MODEL_RELATIONSHIP_STATISTICS',
            360,
        ),
        'recordSegmentStatistics' => env(
            'STICKLE_FREQUENCY_RECORD_SEGMENT_STATISTICS',
            360,
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Which database connection (Defined in config.database) should be use.
    |
    | This must be a Postgres based connection.
    */
    'database' => [
        'schema' => env('STICKLE_DATABASE_SCHEMA', 'public'),
        'tablePrefix' => env('STICKLE_DATABASE_TABLE_PREFIX', 'stc_'),
        'partitionsEnabled' => env('STICKLE_DATABASE_ENABLE_PARTITIONS', true),
        'partitions' => [
            'requests' => [
                'interval' => env(
                    'STICKLE_DATABASE_PARTITIONS_REQUESTS_INTERVAL',
                    'week',
                ),
                'extension' => env(
                    'STICKLE_DATABASE_PARTITIONS_REQUESTS_EXTENSION',
                    '1 week',
                ),
                'retention' => env(
                    'STICKLE_DATABASE_PARTITIONS_REQUESTS_RETENTION',
                    '1 years',
                ),
            ],
            'sessions' => [
                'interval' => env(
                    'STICKLE_DATABASE_PARTITIONS_SESSIONS_INTERVAL',
                    'week',
                ),
                'extension' => env(
                    'STICKLE_DATABASE_PARTITIONS_SESSIONS_EXTENSION',
                    '1 week',
                ),
                'retention' => env(
                    'STICKLE_DATABASE_PARTITIONS_SESSIONS_RETENTION',
                    '1 years',
                ),
            ],
            'model_attribute_audit' => [
                'interval' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_ATTRIBUTE_AUDIT_INTERVAL',
                    'week',
                ),
                'extension' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_ATTRIBUTE_AUDIT_EXTENSION',
                    '1 week',
                ),
                'retention' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_ATTRIBUTE_AUDIT_RETENTION',
                    '1 years',
                ),
            ],
            'segment_statistics' => [
                'interval' => env(
                    'STICKLE_DATABASE_PARTITIONS_SEGMENT_STATISTICS_INTERVAL',
                    'week',
                ),
                'extension' => env(
                    'STICKLE_DATABASE_PARTITIONS_SEGMENT_STATISTICS_EXTENSION',
                    '1 week',
                ),
                'retention' => env(
                    'STICKLE_DATABASE_PARTITIONS_SEGMENT_STATISTICS_RETENTION',
                    '1 years',
                ),
            ],
            'model_relationship_statistics' => [
                'interval' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_RELATIONSHIP_STATISTICS_INTERVAL',
                    'week',
                ),
                'extension' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_RELATIONSHIP_STATISTICS_EXTENSION',
                    '1 week',
                ),
                'retention' => env(
                    'STICKLE_DATABASE_PARTITIONS_MODEL_RELATIONSHIP_STATISTICS_RETENTION',
                    '1 years',
                ),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | Where are certain items located in your Laravel project
    */
    'namespaces' => [
        'segments' => env('STICKLE_NAMESPACES_SEGMENTS', "App\Segments"),
        'listeners' => env('STICKLE_NAMESPACES_LISTENERS', "App\Listeners"),
        'models' => env('STICKLE_NAMESPACES_MODELS', "App\Models"),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filesystem
    |--------------------------------------------------------------------------
    |
    | Stickle needs to save some files, such as exports, usually temporarily.
    | This defines the filesystem disk to use for these files.
    |
    | Only used when segments.useCsvExports is true. The default segment sync
    | runs entirely inside PostgreSQL and writes no files.
    */
    'filesystem' => [
        'disks' => [
            'exports' => env('STICKLE_FILESYSTEM_DISK_EXPORTS', 'local'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Segments
    |--------------------------------------------------------------------------
    |
    | useCsvExports selects how a segment's membership is reconciled.
    |
    | FALSE (default) — SyncSegmentAction reconciles membership in a single
    | SQL statement. Both ends of the operation are the same database, so no
    | file, no external binary, and no shared disk are involved. A failure
    | raises a PDO exception rather than passing silently.
    |
    | TRUE — the legacy ExportSegmentAction/ImportSegmentAction pair: the
    | segment query is written to a CSV, handed to a second queued job, and
    | loaded back with a `psql \copy`. Only enable this if you specifically
    | need it. It carries real operational requirements and known hazards:
    |
    |   * Requires the `psql` binary on every worker that runs
    |     ImportSegmentJob. It is shelled out to via exec(). If psql is absent
    |     the COPY fails, the exit code is discarded, and the reconciliation
    |     proceeds against an EMPTY temp table — which deletes the segment's
    |     entire membership and fires a spurious EXIT for every member. The
    |     job still reports success.
    |
    |   * Requires a shared exports disk. ExportSegmentJob and ImportSegmentJob
    |     are separate queued jobs, so on any multi-instance or containerised
    |     host (Cloud Run, ECS, Kubernetes, multiple queue workers) they will
    |     not share a local filesystem. 'local' works only when a single
    |     machine runs both jobs; anything else needs a shared disk (s3, gcs).
    |
    |   * Leaks the database password. loadTmpTable() interpolates the password
    |     into a shell string as PGPASSWORD=..., where it is visible in the
    |     process list to any local user.
    |
    |   * Is broken by $appends. The export builds each CSV row from the model's
    |     toArray(), which includes appended accessors — so any model declaring
    |     protected $appends emits extra columns and the COPY fails with
    |     "extra data after last expected column". The SQL path never hydrates
    |     models and is unaffected.
    |
    | Both paths are equivalent in result and both are idempotent.
    */
    'segments' => [
        'useCsvExports' => env('STICKLE_USE_CSV_EXPORTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Web and API Routes for Stickle
    */
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

    /*
    |--------------------------------------------------------------------------
    | Broadcast
    |--------------------------------------------------------------------------
    |
    | Settings for the broadcasting of events
    */
    'broadcasting' => [
        'channels' => [
            'firehose' => env(
                'STICKLE_BROADCASTING_CHANNEL_FIREHOSE',
                'stickle.firehose',
            ),
            'object' => env(
                'STICKLE_BROADCASTING_CHANNEL_OBJECT',
                'stickle.object.%s.%s',
            ),
            'class' => env(
                'STICKLE_BROADCASTING_CHANNEL_CLASS',
                'stickle.class.%s',
            ),
        ],

        /*
        | Reverb speaks only the Pusher WebSocket protocol. It does not serve
        | the HTTP fallback endpoints pusher-js would need to degrade to long
        | polling on its own, so the live components poll the requests endpoint
        | whenever the socket is unavailable. Interval is in seconds, and any
        | component accepts a :poll-interval attribute to override it.
        */
        'polling' => [
            'enabled' => env('STICKLE_BROADCASTING_POLLING_ENABLED', true),
            'interval' => env('STICKLE_BROADCASTING_POLLING_INTERVAL', 15),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking
    |--------------------------------------------------------------------------
    |
    | Settings determining the behaviour of the tracking
    */
    'tracking' => [
        'server' => [
            'modelAttributes' => env(
                'STICKLE_TRACK_SERVER_MODEL_ATTRIBUTES',
                true,
            ),
            'loadMiddleware' => env(
                'STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE',
                true,
            ),
            'authenticationEventsTracked' => array_filter(
                explode(
                    ',',
                    /*
                     * Authenticated and Validated are deliberately absent.
                     *
                     * Laravel dispatches Authenticated on every request that
                     * resolves a user -- not once per login -- and Validated on
                     * every credential check. Tracking either roughly doubles
                     * stc_requests and adds no signal the request rows do not
                     * already carry. Both remain valid values here; they are
                     * just not worth their volume as a default.
                     *
                     * A comma-separated list. Every name must appear in
                     * AuthenticatableEventListener::EVENT_CLASSES, and one that
                     * does not now fails at boot rather than silently tracking
                     * nothing. Leave it empty to switch this off.
                     */
                    (string) env(
                        'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED',
                        'CurrentDeviceLogout,Login,Logout,OtherDeviceLogout,PasswordReset,Registered,Verified',
                    ),
                ),
            ),
        ],
        'client' => [
            'loadMiddleware' => env(
                'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE',
                true,
            ),
        ],
    ],
];

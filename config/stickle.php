<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduling Frequencies
    |--------------------------------------------------------------------------
    |
    | How frequently (in minutes) should the various tasks be run.
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
        'recordModelAttributes' => env('STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES', 360),
        'recordModelRelationshipStatistics' => env('STICKLE_FREQUENCY_RECORD_MODEL_RELATIONSHIP_STATISTICS', 360),
        'recordSegmentStatistics' => env('STICKLE_FREQUENCY_RECORD_SEGMENT_STATISTICS', 360),
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
        'tablePrefix' => env('STICKLE_DATABASE_TABLE_PREFIX', ''),
        'partitionsEnabled' => env('STICKLE_DATABASE_ENABLE_PARTITIONS', true),
        'partitions' => [
            'events' => [
                'interval' => env('STICKLE_DATABASE_PARTITIONS_EVENTS_INTERVAL', 'week'),
                'extension' => env('STICKLE_DATABASE_PARTITIONS_EVENTS_EXTENSION', '1 week'),
                'retention' => env('STICKLE_DATABASE_PARTITIONS_EVENTS_RETENTION', '1 years'),
            ],
            'requests' => [
                'interval' => env('STICKLE_DATABASE_PARTITIONS_REQUESTS_INTERVAL', 'week'),
                'extension' => env('STICKLE_DATABASE_PARTITIONS_REQUESTS_EXTENSION', '1 week'),
                'retention' => env('STICKLE_DATABASE_PARTITIONS_REQUESTS_RETENTION', '1 years'),
            ],
            'sessions' => [
                'interval' => env('STICKLE_DATABASE_PARTITIONS_SESSIONS_INTERVAL', 'week'),
                'extension' => env('STICKLE_DATABASE_PARTITIONS_SESSIONS_EXTENSION', '1 week'),
                'retention' => env('STICKLE_DATABASE_PARTITIONS_SESSIONS_RETENTION', '1 years'),
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
        'segments' => env('STICKLE_NAMESPACES_SEGMENTS', 'App\Segments'),
        'listeners' => env('STICKLE_NAMESPACES_LISTENERS', 'App\Listeners'),
        'models' => env('STICKLE_NAMESPACES_MODELS', 'App\Models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filesystem
    |--------------------------------------------------------------------------
    |
    | Stickle needs to save some files, such as exports, usually temporarily.
    | This defines the filesystem disk to use for these files.
    */
    'filesystem' => [
        'disks' => [
            'exports' => env('STICKLE_FILESYSTEM_DISK_EXPORTS', 'local'),
        ],
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
            'middleware' => env('STICKLE_API_MIDDLEWARE', ['api']),
        ],
        'web' => [
            'prefix' => env('STICKLE_WEB_PREFIX', 'stickle'),
            'middleware' => env('STICKLE_WEB_MIDDLEWARE', ['web']),
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
            'firehose' => env('STICKLE_BROADCASTING_CHANNEL_FIREHOSE', 'stickle.firehose'),
            'object' => env('STICKLE_BROADCASTING_CHANNEL_OBJECT', 'stickle.object.%s.%s'),
            'class' => env('STICKLE_BROADCASTING_CHANNEL_CLASS', 'stickle.class.%s'),
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
            'loadMiddleware' => env('STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE', true),
            'authenticationEventsTracked' => explode(
                ',',
                env('STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED', 'Authenticated,CurrentDeviceLogout,Login,Logout,OtherDeviceLogout,PasswordReset,Registered,Validated,Verified')
            ),
        ],
        'client' => [
            'loadMiddleware' => env('STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE', true),
        ],
    ],
];

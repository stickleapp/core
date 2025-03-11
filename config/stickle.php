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
        'recordSegmentStatistics' => env('STICKLE_FREQUENCY_EXPORT_SEGMENT_STATISTICS', 360),
        'recordEntityStatistics' => env('STICKLE_FREQUENCY_EXPORT_ENTITY_STATISTICS', 360),
        'rollupEvents' => env('STICKLE_FREQUENCY_ROLLUP_EVENTS', 360),
        'rollupPageViews' => env('STICKLE_FREQUENCY_ROLLUP_PAGEVIEWS', 360),
        'rollupSessions' => env('STICKLE_FREQUENCY_ROLLUP_SESSIONS', 360),
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
        'schema' => env('STICKLE_SCHEMA', 'public'),
        'connection' => env('DB_CONNECTION', 'pgsql'),
        'tablePrefix' => env('STICKLE_TABLE_PREFIX', 'stc_'),
        'partitions' => [
            'events' => [
                'interval' => 'week',
                'extension' => '1 week',
                'retention' => '1 years',
            ],
            'page_views' => [
                'interval' => 'week',
                'extension' => '1 week',
                'retention' => '1 years',
            ],
            'sessions' => [
                'interval' => 'week',
                'extension' => '1 week',
                'retention' => '1 years',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Which models should be used for the various entities and what is the relationship
    | between the group and the user
    */
    'models' => [
        // @phpstan-ignore class.notFound
        'user' => env('AUTH_MODEL', App\Models\User::class),
        'group' => env('STICKLE_MODEL_GROUP', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Parent Child Relationship
    |--------------------------------------------------------------------------
    |
    | Which models should be used for the various entities and what is the relationship
    | between the group(S) and the user(s)
    */
    'relationship' => \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Where are certain items located in your Laravel project
    */
    'paths' => [
        'segments' => env('STICKLE_PATH_SEGMENTS', 'App\Segments'),
        'listeners' => env('STICKLE_PATH_LISTENERS', 'App\Listeners'),
        'models' => env('STICKLE_PATH_MODELS', 'App\Models'),
    ],

    'storage' => [
        'exports' => env('STICKLE_STORAGE_EXPORTS', 'segment-exports'),
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
            'loadMiddleware' => env('stickle_TRACK_SERVER_LOAD_MIDDLEWARE', true),
            'trackAuthenticationEvents' => env('stickle_TRACK_SERVER_AUTHENTICATION_EVENTS', true),
            'authenticationEventsTracked' => [
                'Authenticated',
                'CurrentDeviceLogout',
                'Login',
                'Logout',
                'OtherDeviceLogout',
                'PasswordReset',
                'Registered',
                'Validated',
                'Verified',
            ],
            'trackRequests' => env('stickle_TRACK_SERVER_REQUESTS', true),
        ],
        'client' => [
            'loadMiddleware' => env('stickle_TRACK_CLIENT_LOAD_MIDDLEWARE', true),
            'trackPageViews' => env('stickle_TRACK_CLIENT_PAGE_VIEWS', true),
            'controllerPrefix' => env('stickle_TRACK_CONTROLLER_PREFIX', ''),
        ],
    ],
];

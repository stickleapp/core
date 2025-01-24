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
        'exportSegments' => env('CASACADE_FREQUENCY_EXPORT_SEGMENTS', 360),
        'recordSegmentStatistics' => env('CASACADE_FREQUENCY_EXPORT_SEGMENT_STATISTICS', 360),
        'recordEntityStatistics' => env('CASACADE_FREQUENCY_EXPORT_ENTITY_STATISTICS', 360),
        'rollupEvents' => env('CASACADE_FREQUENCY_ROLLUP_EVENTS', 360),
        'rollupPageViews' => env('CASACADE_FREQUENCY_ROLLUP_PAGEVIEWS', 360),
        'rollupSessions' => env('CASACADE_FREQUENCY_ROLLUP_SESSIONS', 360),
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
        'connection' => env('DB_CONNECTION', 'pgsql'),
        'tablePrefix' => env('`CASACADE_TABLE_PRFIX`', 'lc_'),
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
        'group' => env('CASACADE_MODEL_GROUP', null),
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
        'segments' => env('CASACADE_PATH_SEGMENTS', 'App\Segments'),
        'listeners' => env('CASACADE_PATH_LISTENERS', 'App\Listeners'),
        'models' => env('CASACADE_PATH_MODELS', 'App\Models'),
    ],

    'storage' => [
        'exports' => env('CASACADE_STORAGE_EXPORTS', 'segment-exports'),
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
            'loadMiddleware' => env('CASCADE_TRACK_SERVER_LOAD_MIDDLEWARE', true),
            'trackAuthenticationEvents' => env('CASCADE_TRACK_SERVER_AUTHENTICATION_EVENTS', true),
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
            'trackRequests' => env('CASCADE_TRACK_SERVER_REQUESTS', true),
        ],
        'client' => [
            'loadMiddleware' => env('CASCADE_TRACK_CLIENT_LOAD_MIDDLEWARE', true),
            'trackPageViews' => env('CASCADE_TRACK_CLIENT_PAGE_VIEWS', true),
            'controllerPrefix' => env('CASCADE_TRACK_CONTROLLER_PREFIX', ''),
        ],
    ],
];

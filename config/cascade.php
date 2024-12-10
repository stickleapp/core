<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduling Frequencies
    |--------------------------------------------------------------------------
    |
    | How many minutes should elapse before running scheduled tasks
    |
    */
    'schedule' => [
        'ExportSegments' => env('CASACADE_FREQUENCY_EXPORT_SEGMENTS', 360),
        'RecordSegmentStatistics' => env('CASACADE_FREQUENCY_EXPORT_SEGMENT_STATISTICS', 360),
        'RecordEntityStatistics' => env('CASACADE_FREQUENCY_EXPORT_ENTITY_STATISTICS', 360),
        'RollupEvents' => env('CASACADE_FREQUENCY_ROLLUP_EVENTS', 360),
        'RollupPageViews' => env('CASACADE_FREQUENCY_ROLLUP_PAGEVIEWS', 360),
        'RollupSessions' => env('CASACADE_FREQUENCY_ROLLUP_SESSIONS', 360),
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
        'tablePrefix' => env('CASACADE_TABLE_PRFIX', 'lc_'),
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

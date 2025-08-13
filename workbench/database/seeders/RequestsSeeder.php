<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RequestsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}requests public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}requests_rollup_1min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}requests_rollup_5min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}requests_rollup_1hr public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}requests_rollup_1day public week '{$date}' 2");

        $sql = <<<SQL
INSERT INTO {$prefix}requests (
    object_uid, 
    model_class, 
    session_uid, 
    type,
    properties,
    timestamp)
SELECT
    (((SELECT MAX(id) FROM users) * random())+1)::INT::TEXT AS object_uid,
    'User' AS model_class,
    'session_' || (random() * 90)::int::text AS session_uid,
    'request' AS type,
    jsonb_build_object(
        'url', 'http://example.com',
        'path', '/path/to/page',
        'host', 'example.com',
        'search', 'search',
        'query_params', 'query_params',
        'utm_source', 'utm_source',
        'utm_medium', 'utm_medium',
        'utm_campaign', 'utm_campaign',
        'utm_content', 'utm_content'
    ) AS properties,
    CURRENT_TIMESTAMP - (random() * interval '19 days') AS timestamp
FROM
    generate_series(1,1e3) AS s;

INSERT INTO {$prefix}requests (
    object_uid, 
    model_class, 
    session_uid,
    type, 
    properties, 
    timestamp)
SELECT
    (((SELECT MAX(id) FROM users) * random())+1)::INT::TEXT AS object_uid,
    'User' AS model_class,
    'session_' || (random() * 90)::int::text AS session_uid,
    'event' AS type,
    jsonb_build_object(
        'name', 'clicked:thing',
        'url', 'http://example.com',
        'path', '/path/to/page',
        'host', 'example.com',
        'search', 'search',
        'query_params', 'query_params',
        'utm_source', 'utm_source',
        'utm_medium', 'utm_medium',
        'utm_campaign', 'utm_campaign',
        'utm_content', 'utm_content'
    ) AS properties,
    CURRENT_TIMESTAMP - (random() * interval '19 days') AS timestamp
FROM
    generate_series(1,1e3) AS s;
-- ----------------------------------------------------------------------------
-- RUN AGGREGATION QUERIES
-- ----------------------------------------------------------------------------
SELECT {$prefix}rollup_requests_1min();
SELECT {$prefix}rollup_requests_5min();
SELECT {$prefix}rollup_requests_1hr();
SELECT {$prefix}rollup_requests_1day();
SQL;

        DB::unprepared($sql);
    }
}

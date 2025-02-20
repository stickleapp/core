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
    model, 
    session_uid, 
    url,
    path,
    host,
    search,
    query_params,
    utm_source,
    utm_medium,
    utm_campaign,
    utm_content,
    timestamp)
SELECT
    (((SELECT MAX(id) FROM users) * random())+1)::INT::TEXT AS object_uid,
    '\App\Models\User' AS model,
    'session_' || (random() * 90)::int::text AS session_uid,
    'http://example.com' AS url,
    '/path/to/page' AS path,
    'example.com' AS host,
    'search' AS search,
    'query_params' AS query_params,
    'utm_source' AS utm_source,
    'utm_medium' AS utm_medium,
    'utm_campaign' AS utm_campaign,
    'utm_content' AS utm_content,
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

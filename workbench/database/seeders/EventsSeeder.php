<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}events public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_5min public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1hr public week '{$date}' 2");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1day public week '{$date}' 2");

        $sql = <<<SQL
INSERT INTO {$prefix}events (
    object_uid, 
    model_class, 
    session_uid, 
    event_name, 
    properties, 
    timestamp)
SELECT
    (((SELECT MAX(id) FROM users) * random())+1)::INT::TEXT AS object_uid,
    'User' AS model_class,
    'session_' || (random() * 90)::int::text AS session_uid,
    'event_' || (random() * 10)::int::text AS event_name,    
    jsonb_build_object(
        'property1', 'value1',
        'property2', 'value2',
        'property3', (random() * 1000)::int
    ) AS properties,
    CURRENT_TIMESTAMP - (random() * interval '19 days') AS timestamp
FROM
    generate_series(1,1e3) AS s;

-- ----------------------------------------------------------------------------
-- RUN AGGREGATION QUERIES
-- ----------------------------------------------------------------------------
SELECT {$prefix}rollup_events_1min();
SELECT {$prefix}rollup_events_5min();
SELECT {$prefix}rollup_events_1hr();
SELECT {$prefix}rollup_events_1day();
SQL;

        DB::unprepared($sql);
    }
}

<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = config('stickle.database.tablePrefix');

        Artisan::call("stickle:create-partitions {$prefix}events public week '2024-08-01'");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1min public week '2024-08-01'");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_5min public week '2024-08-01'");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1hr public week '2024-08-01'");
        Artisan::call("stickle:create-partitions {$prefix}events_rollup_1day public week '2024-08-01'");

        $sql = <<<SQL
INSERT INTO {$prefix}events (
    object_uid, 
    model, 
    -- session_uid, 
    event_name, 
    properties, 
    timestamp)
SELECT
    (random() * 1000)::int::text AS object_uid,
    '\App\Models\User' AS model,
    -- 'session_' || (random() * 20)::int::text AS session_uid,
    'event_' || (random() * 10)::int::text AS event_name,    
    jsonb_build_object(
        'property1', 'value1',
        'property2', 'value2',
        'property3', (random() * 1000)::int
    ) AS properties,
    CURRENT_TIMESTAMP - (random() * interval '19 days') AS timestamp
FROM
    generate_series(1,1e6) AS s;

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

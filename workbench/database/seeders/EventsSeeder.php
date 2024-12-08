<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sql = <<<'sql'

INSERT INTO lc_events (
    object_uid, 
    model, 
    -- session_uid, 
    event_name, 
    properties, 
    timestamp)
SELECT
    -- Generate random object uid  
    'person_' || (random() * 1000)::int::text AS object_uid,
    -- model
    '\App\Models\User' AS model,
    -- Generate a random session_uid
    -- 'session_' || (random() * 20)::int::text AS session_uid,
    -- Generate random event name
    'event_' || (random() * 10)::int::text AS event_name,    
    -- Generate random properties (JSONB)
    jsonb_build_object(
        'property1', 'value1',
        'property2', 'value2',
        'property3', (random() * 1000)::int
    ) AS properties,
    -- Generate random timestamp within the specified range
    CURRENT_TIMESTAMP - (random() * interval '19 days') AS timestamp
FROM
    generate_series(1,1e6) AS s;
sql;
        DB::unprepared($sql);
    }
}

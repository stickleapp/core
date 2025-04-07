<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ModelRelationshipStatisticsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}model_relationship_statistics public week '{$date}' 2");

        $sql = <<<sql
INSERT INTO {$prefix}model_relationship_statistics (model, object_uid, relationship, attribute, value_min, value_max, value_avg, value_count, value_sum, recorded_at)
SELECT 
    'Workbench\App\Models\Customer' AS model,
    object_uid, 
    'children' AS relationship,
    attribute, 
    random() * 100 AS value_min, 
    random() * 100 AS value_max, 
    random() * 100 AS value_avg, 
    (random() * 100)::int AS value_count, 
    (random() * 1000) AS value_sum,
    date::date AS recorded_at
FROM 
    (SELECT 
        object_uid, 
        attribute, 
        generate_series(now()::date - interval '12 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}model_attributes,
        (VALUES 
            ('mrr'), 
            ('ticket_count'),
            ('open_ticket_count'),
            ('closed_ticket_count'),
            ('tickets_closed_last_30_days'),
            ('average_resolution_time'),
            ('average_resolution_time_30_days')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\Customer'
    ) AS series
ON CONFLICT DO NOTHING;

INSERT INTO {$prefix}model_relationship_statistics (model, object_uid, relationship, attribute, value_min, value_max, value_avg, value_count, value_sum, recorded_at)
SELECT 
    'Workbench\App\Models\Customer' AS model,
    object_uid, 
    'users' AS relationship,
    attribute, 
    random() * 100 AS value_min, 
    random() * 100 AS value_max, 
    random() * 100 AS value_avg, 
    (random() * 100)::int AS value_count, 
    (random() * 1000) AS value_sum,
    date::date AS recorded_at
FROM 
    (SELECT 
        object_uid, 
        attribute, 
        generate_series(now()::date - interval '12 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}model_attributes,
        (VALUES 
            ('user_rating'), 
            ('ticket_count'),
            ('open_ticket_count'),
            ('closed_ticket_count'),
            ('tickets_closed_last_30_days'),
            ('average_resolution_time'),
            ('average_resolution_time_30_days')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\Customer'
    ) AS series
ON CONFLICT DO NOTHING;
sql;

        DB::unprepared($sql);
    }
}

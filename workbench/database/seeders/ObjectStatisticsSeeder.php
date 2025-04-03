<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ObjectStatisticsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}object_statistics public week '{$date}' 2");

        $sql = <<<sql
INSERT INTO {$prefix}object_statistics (model, object_uid, attribute, value_min, value_max, value_avg, value_count, value_sum, recorded_at)
SELECT 
    'Workbench\App\Models\Customer' AS model,
    object_uid, 
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
        generate_series(now()::date - interval '24 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}object_attributes,
        (VALUES 
            ('__users.user_rating'), 
            ('__users.ticket_count'),
            ('__users.open_ticket_count'),
            ('__users.closed_ticket_count'),
            ('__users.tickets_closed_last_30_days'),
            ('__users.average_resolution_time'),
            ('__users.average_resolution_time_30_days'),
            ('__groups.mrr'), 
            ('__groups.ticket_count'),
            ('__groups.open_ticket_count'),
            ('__groups.closed_ticket_count'),
            ('__groups.tickets_closed_last_30_days'),
            ('__groups.average_resolution_time'),
            ('__groups.average_resolution_time_30_days'),
            ('__groups.__users.user_rating'), 
            ('__groups.__users.ticket_count'),
            ('__groups.__users.open_ticket_count'),
            ('__groups.__users.closed_ticket_count'),
            ('__groups.__users.tickets_closed_last_30_days'),
            ('__groups.__users.average_resolution_time'),
            ('__groups.__users.average_resolution_time_30_days')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\Customer'
    ) AS series
ON CONFLICT DO NOTHING;
sql;

        DB::unprepared($sql);
    }
}

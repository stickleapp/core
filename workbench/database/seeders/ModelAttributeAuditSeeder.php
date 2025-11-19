<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ModelAttributeAuditSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}model_attribute_audit public week '{$date}' 5");

        $sql = <<<sql
INSERT INTO {$prefix}model_attribute_audit (
    model_class, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    'Customer' AS model_class,
    object_uid, 
    attribute, 
    random() * 100 AS value_old,
    random() * 100 AS value_new,
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
        WHERE model_class = 'Customer'
    ) AS series
ON CONFLICT DO NOTHING;

INSERT INTO {$prefix}model_attribute_audit (
    model_class, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    'User' AS model_class,
    object_uid, 
    attribute, 
    random() * 100 AS value_old,
    random() * 100 AS value_new,
    date::date AS recorded_at
FROM 
    (SELECT 
        object_uid, 
        attribute, 
        generate_series(now()::date - interval '12 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}model_attributes,
        (VALUES 
            ('user_level'),
            ('user_rating'),
            ('ticket_count'),
            ('open_ticket_count'),
            ('closed_ticket_count'),
            ('tickets_closed_last_30_days'),
            ('average_resolution_time'),
            ('average_resolution_time_30_days')  
        ) AS attributes(attribute)
        WHERE model_class = 'User'
    ) AS series
ON CONFLICT DO NOTHING;
sql;

        DB::unprepared($sql);
    }
}

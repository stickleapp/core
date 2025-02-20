<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ObjectAttributesAuditSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}object_attributes_audit public week '{$date}' 2");

        $sql = <<<sql
INSERT INTO {$prefix}object_attributes_audit (
    model, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    '\App\Models\Customer' AS model,
    object_uid, 
    attribute, 
    random() * 100 AS value_old,
    random() * 100 AS value_new,
    date::date AS recorded_at
FROM 
    (SELECT 
        object_uid, 
        attribute, 
        generate_series(now()::date - interval '24 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}object_attributes,
        (VALUES 
            ('mrr')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\Customer'
    ) AS series
ON CONFLICT DO NOTHING;

INSERT INTO {$prefix}object_attributes_audit (
    model, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    '\App\Models\User' AS model,
    object_uid, 
    attribute, 
    random() * 100 AS value_old,
    random() * 100 AS value_new,
    date::date AS recorded_at
FROM 
    (SELECT 
        object_uid, 
        attribute, 
        generate_series(now()::date - interval '24 days', now()::date, interval '1 day') AS date
        FROM 
        {$prefix}object_attributes,
        (VALUES 
            ('user_rating'), 
            ('order_count'), 
            ('order_item_count')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\User'
    ) AS series
ON CONFLICT DO NOTHING;
sql;

        DB::unprepared($sql);
    }
}

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

        Artisan::call("stickle:create-partitions {$prefix}model_attribute_audit public week '{$date}' 2");

        $sql = <<<sql
INSERT INTO {$prefix}model_attribute_audit (
    model, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    'Workbench\App\Models\Customer' AS model,
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
            ('mrr')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\Customer'
    ) AS series
ON CONFLICT DO NOTHING;

INSERT INTO {$prefix}model_attribute_audit (
    model, 
    object_uid, 
    attribute,
    value_old,
    value_new,
    timestamp)
SELECT 
    'Workbench\App\Models\User' AS model,
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
            ('user_rating')
        ) AS attributes(attribute)
        WHERE model = 'Workbench\App\Models\User'
    ) AS series
ON CONFLICT DO NOTHING;
sql;

        DB::unprepared($sql);
    }
}

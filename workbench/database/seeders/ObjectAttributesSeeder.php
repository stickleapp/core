<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ObjectAttributesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = config('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}object_attributes_audit public week '{$date}' 2");

        $sql = <<<sql
DELETE FROM {$prefix}object_attributes;
INSERT INTO {$prefix}object_attributes (
	model,
	object_uid,
	model_attributes,
	synced_at,
	created_at,
	updated_at
)
SELECT
	'Workbench\App\Models\Customer' as model,
	customers.id AS object_uid,
	'{
  "mrr": 99,
  "__users": {
    "user_rating": {
      "value": null,
      "min": 1,
      "max": 5,
      "avg": 4.78,
      "count": 23
    },
    "order_count": {
      "value": null,
      "min": 1,
      "max": 8,
      "avg": 1.2,
      "count": 14
    },
    "order_item_count": {
      "value": null,
      "min": 1,
      "max": 47,
      "avg": 1.92,
      "count": 14
    }
  },
  "__groups": {
    "mrr": {
      "value": null,
      "min": 188,
      "max": 599,
      "avg": 478,
      "count": 3
    },
    "__users": {
      "user_rating": {
        "value": null,
        "min": 1,
        "max": 5,
        "avg": 4.78,
        "count": 23
      },
      "order_count": {
        "value": null,
        "min": 1,
        "max": 12,
        "avg": 1.2,
        "count": 23
      },
      "order_item_count": {
        "value": null,
        "min": 1,
        "max": 78,
        "avg": 2.2,
        "count": 23
      }
    }
  }
}'::jsonb AS model_attributes,
	CURRENT_TIMESTAMP as synced_at,
	CURRENT_TIMESTAMP as created_at,
	CURRENT_TIMESTAMP as updated_at
FROM
	customers
ON CONFLICT (modeL, object_uid) 
DO UPDATE SET
  model_attributes = EXCLUDED.model_attributes;

INSERT INTO {$prefix}object_attributes (
	model,
	object_uid,
	model_attributes,
	synced_at,
	created_at,
	updated_at
)
SELECT
	'Workbench\App\Models\User' as model,
	users.id AS object_uid,
	CASE WHEN LENGTH(users.id::TEXT) = 4 THEN '{"user_rating":1, "order_count": 3, "order_item_count": 8}'::jsonb
        WHEN LENGTH(users.id::TEXT) = 3 THEN '{"user_rating":2, "order_count": 2, "order_item_count": 5}'::jsonb
        WHEN LENGTH(users.id::TEXT) = 2 THEN '{"user_rating":3, "order_count": 6, "order_item_count": 18}'::jsonb 
        ELSE '{}'::jsonb
    END AS model_attributes,
	CURRENT_TIMESTAMP as synced_at,
	CURRENT_TIMESTAMP as created_at,
	CURRENT_TIMESTAMP as updated_at
FROM
	users
ON CONFLICT (modeL, object_uid) 
DO UPDATE SET
  model_attributes = EXCLUDED.model_attributes;
sql;

        DB::unprepared($sql);
    }
}

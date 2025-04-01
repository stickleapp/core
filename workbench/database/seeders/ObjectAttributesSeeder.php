<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;
use Workbench\App\Models\User;

class ObjectAttributesSeeder extends Seeder
{

//   "__users": {
//     "user_rating": {
//       "value": null,
//       "min": 1,
//       "max": 5,
//       "avg": 4.78,
//       "count": 23
//     },
//     "order_count": {
//       "value": null,
//       "min": 1,
//       "max": 8,
//       "avg": 1.2,
//       "count": 14
//     },
//     "order_item_count": {
//       "value": null,
//       "min": 1,
//       "max": 47,
//       "avg": 1.92,
//       "count": 14
//     }
//   },
//   "__groups": {
//     "mrr": {
//       "value": null,
//       "min": 188,
//       "max": 599,
//       "avg": 478,
//       "count": 3
//     },
//     "__users": {
//       "user_rating": {
//         "value": null,
//         "min": 1,
//         "max": 5,
//         "avg": 4.78,
//         "count": 23
//       },
//       "order_count": {
//         "value": null,
//         "min": 1,
//         "max": 12,
//         "avg": 1.2,
//         "count": 23
//       },
//       "order_item_count": {
//         "value": null,
//         "min": 1,
//         "max": 78,
//         "avg": 2.2,
//         "count": 23
//       }
//     }
//   }
// }'::jsonb AS model_attributes,
  
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}object_attributes_audit public week '{$date}' 2");

        DB::table("{$prefix}object_attributes")->truncate();

        $customers = Customer::has('users')->get()->take(500);

        $stickleTrackedAttributes = Customer::$stickleTrackedAttributes ?? [];

        foreach ($customers as $customer) {

            $attributes = [];

            foreach ($stickleTrackedAttributes as $attribute) {
              $attributes[$attribute] = $customer->{$attribute} ?? null;
            }

            // Replace insert with upsert to handle conflicts
            DB::table("{$prefix}object_attributes")->insert([
              'model' => 'Workbench\App\Models\Customer',
              'object_uid' => $customer->id,
              'model_attributes' => json_encode($attributes),
              'synced_at' => now(),
              'created_at' => now(),
              'updated_at' => now(),
            ]);

            $users = $customer->users;

            $stickleTrackedAttributes = User::$stickleTrackedAttributes ?? [];

            foreach  ($users as $user) {
                $attributes = [];

                foreach ($stickleTrackedAttributes as $attribute) {
                  $attributes[$attribute] = $user->{$attribute} ?? null;
                }

                // Replace insert with upsert to handle conflicts
                DB::table("{$prefix}object_attributes")->insert([
                  'model' => 'Workbench\App\Models\User',
                  'object_uid' => $user->id,
                  'model_attributes' => json_encode($attributes),
                  'synced_at' => now(),
                  'created_at' => now(),
                  'updated_at' => now(),
                ]);
            }
        }
    }
}

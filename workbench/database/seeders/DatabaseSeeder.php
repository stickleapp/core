<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = config('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}model_attribute_audit public week '{$date}' 2");

        $this->call([
            CustomersSeeder::class,
            UsersSeeder::class,
            SubscriptionsSeeder::class,
            TicketsSeeder::class,
            ModelSegmentsSeeder::class,
            ModelAttributeSeeder::class,
            ModelAttributeAuditSeeder::class,
            ModelRelationshipStatisticsSeeder::class,
            RequestsSeeder::class,
            SessionsSeeder::class,
            SegmentsSeeder::class,
            SegmentStatisticsSeeder::class,
        ]);
    }
}

<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(25)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}object_attributes_audit public week '{$date}' 2");

        $this->call([
            CustomersSeeder::class,
            UsersSeeder::class,
            ObjectSegmentsSeeder::class,
            ObjectAttributesSeeder::class,
            ObjectAttributesAuditSeeder::class,
            ObjectStatisticsSeeder::class,
            EventsSeeder::class,
            RequestsSeeder::class,
            OrdersSeeder::class,
            SegmentsSeeder::class,
            SegmentStatisticsSeeder::class,
            SessionsSeeder::class,
        ]);
    }
}

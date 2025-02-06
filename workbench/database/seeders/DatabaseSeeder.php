<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CustomersSeeder::class,
            UsersSeeder::class,
            EventsSeeder::class,
            RequestsSeeder::class,
            OrdersSeeder::class,
            SegmentStatisticsSeeder::class,
        ]);
    }
}

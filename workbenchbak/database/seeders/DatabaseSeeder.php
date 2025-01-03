<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UsersSeeder::class,
            // EventsSeeder::class,
            // RequestsSeeder::class,
            // OrdersSeeder::class,
            // SegmentStatisticsSeeder::class,
        ]);
    }
}

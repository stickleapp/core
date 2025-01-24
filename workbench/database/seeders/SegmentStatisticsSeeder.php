<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class SegmentStatisticsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $prefix = config('stickle.database.tablePrefix');

        Artisan::call("cascade:create-partitions {$prefix}segment_statistics public week '2024-12-01'");
    }
}

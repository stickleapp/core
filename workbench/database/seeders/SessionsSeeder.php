<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SessionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $date = now()->subDays(90)->toDateString();

        Artisan::call("stickle:create-partitions {$prefix}sessions_rollup_1day public week '{$date}' 2");

        Artisan::call("stickle:rollup-sessions '{$date}'");
    }
}

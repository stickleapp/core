<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

// use Illuminate\Support\Facades\DB;

class ModelSegmentsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('stickle:export-segments /Users/davidclaysmith/Projects/StickleApp/Core/workbench/app/Segments \\\\Workbench\\\\App\\\\Segments 10');
    }
}

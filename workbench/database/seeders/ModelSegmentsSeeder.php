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
        Artisan::call('stickle:export-segments Workbench\\\\App\\\\Segments 10 /Users/davidclaysmith/Projects/StickleApp/Core/workbench/app/Segments');
    }
}

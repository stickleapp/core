<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $prefix = config('stickle.database.tablePrefix');

        DB::unprepared('CREATE EXTENSION IF NOT EXISTS postgis');

        Schema::create("{$prefix}location_data", function (Blueprint $blueprint): void {
            $blueprint->text('ip_address')->primary();
            $blueprint->text('city')->nullable(false);
            $blueprint->text('country')->nullable(false);
            $blueprint->geography('coordinates', subtype: 'point', srid: 4326);

            $blueprint->timestamps();

            $blueprint->spatialIndex('coordinates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        $prefix = config('stickle.database.tablePrefix');

        DB::unprepared("DROP TABLE IF EXISTS {$prefix}location_data");
    }
};

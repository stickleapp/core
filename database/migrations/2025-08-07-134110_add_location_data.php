<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Schema::create("{$prefix}location_data", function (Blueprint $table) {
            $table->id();
            $table->text('ip_address')->nullable(false);
            $table->text('city')->nullable(false);
            $table->text('country')->nullable(false);
            $table->geography('coordinates', subtype: 'point', srid: 4326);

            $table->timestamps();

            $table->unique(['ip_address']);
            $table->spatialIndex('coordinates');
        });

        Schema::table("{$prefix}requests", function (Blueprint $table) {
            $table->text('ip_address')->nullable(true);
            $table->index(['ip_address']);
        });

        Schema::table("{$prefix}events", function (Blueprint $table) {
            $table->text('ip_address')->nullable(true);
            $table->index(['ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        $prefix = config('stickle.database.tablePrefix');

        DB::unprepared("DROP TABLE IF EXISTS {$prefix}location_data");

        Schema::table("{$prefix}events", function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });

        Schema::table("{$prefix}requests", function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};

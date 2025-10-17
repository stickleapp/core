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
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::create("{$prefix}segment_statistic_exports", function (Blueprint $blueprint) use ($prefix): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('segment_id')->nullable(false);
            $blueprint->text('attribute')->nullable(false);
            $blueprint->timestamp('last_recorded_at')->nullable(false);
            $blueprint->timestamps();

            $blueprint->foreign('segment_id')->references('id')->on("{$prefix}segments");
            $blueprint->unique(['segment_id', 'attribute']);
        });

        DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}segment_statistics;
CREATE TABLE {$prefix}segment_statistics (
    id BIGSERIAL,
    segment_id BIGINT NOT NULL,
    attribute TEXT NOT NULL,
    value FLOAT NULL,
    value_avg FLOAT NULL,
    value_count FLOAT NULL,
    value_max FLOAT NULL,
    value_min FLOAT NULL,
    value_sum FLOAT NULL,
    recorded_at DATE NOT NULL
) PARTITION BY RANGE (recorded_at);

CREATE INDEX ON {$prefix}segment_statistics (recorded_at);

CREATE UNIQUE INDEX {$prefix}segment_statistics_segment_id_attribute_recorded_at_unique ON {$prefix}segment_statistics (segment_id, attribute, recorded_at);
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::dropIfExists("{$prefix}segment_statistics");

        Schema::dropIfExists("{$prefix}segment_statistic_exports");
    }
};

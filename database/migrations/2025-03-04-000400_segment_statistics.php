<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::create("{$prefix}segment_statistic_exports", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->timestamp('last_recorded_at')->nullable(false);
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on("{$prefix}segments");
            $table->unique(['segment_id', 'attribute']);
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
     *
     * @return void
     */
    public function down()
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::dropIfExists("{$prefix}segment_statistics");

        Schema::dropIfExists("{$prefix}segment_statistic_exports");
    }
};

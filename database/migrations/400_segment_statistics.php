<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected string $prefix;

    public function __construct(
        ?string $prefix = null
    ) {
        $this->prefix = $prefix ?? '';
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}segment_statistics;
CREATE TABLE {$prefix}segment_statistics (
    id BIGSERIAL,
    segment_id BIGINT NOT NULL,
    attribute TEXT NOT NULL,
    value FLOAT NULL,
    value_count FLOAT NULL,
    value_sum FLOAT NULL,
    value_min FLOAT NULL,
    value_max FLOAT NULL,
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
        $prefix = $this->prefix;

        Schema::dropIfExists("{$prefix}segment_statistics");
    }
};

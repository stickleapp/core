    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}object_statistics;
CREATE TABLE {$prefix}object_statistics (
    id BIGSERIAL,
    model TEXT NOT NULL,
    object_uid TEXT NOT NULL,
    attribute TEXT NOT NULL,
    value FLOAT NULL,
    value_count FLOAT NULL,
    value_sum FLOAT NULL,
    value_avg FLOAT NULL,
    value_min FLOAT NULL,
    value_max FLOAT NULL,
    recorded_at DATE NOT NULL
) PARTITION BY RANGE (recorded_at);

CREATE INDEX ON {$prefix}object_statistics (recorded_at);

CREATE UNIQUE INDEX {$prefix}object_statistics_model_object_uid_attribute_recorded_at_unique ON {$prefix}object_statistics (model, object_uid, attribute, recorded_at);
");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        Schema::dropIfExists("{$prefix}object_statistics");
    }
};

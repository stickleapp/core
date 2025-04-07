<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
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

<<<<<<< HEAD:database/migrations/2025-03-04-000500_model_relationship_statistics.php
        Schema::create("{$prefix}model_relationship_statistic_exports", function (Blueprint $table) {
=======
        Schema::create("{$prefix}object_statistic_exports", function (Blueprint $table) {
>>>>>>> 4a8290cedd927491a35310724eae633096ca9bd6:database/migrations/2025-03-04-000500_object_statistics.php
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('relationship')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->timestamp('last_recorded_at')->nullable(false);
            $table->timestamps();

            $table->unique(['model', 'relationship', 'attribute']);
        });

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}model_relationship_statistics;
CREATE TABLE {$prefix}model_relationship_statistics (
    id BIGSERIAL,
    model TEXT NOT NULL,
    object_uid TEXT NOT NULL,
    relationship TEXT NOT NULL,
    attribute TEXT NOT NULL,
    value FLOAT NULL,
    value_count FLOAT NULL,
    value_sum FLOAT NULL,
    value_avg FLOAT NULL,
    value_min FLOAT NULL,
    value_max FLOAT NULL,
    recorded_at DATE NOT NULL
) PARTITION BY RANGE (recorded_at);

CREATE INDEX ON {$prefix}model_relationship_statistics (recorded_at);

CREATE UNIQUE INDEX {$prefix}model_relationship_statistics_model_object_uid_attribute_recorded_at_unique ON {$prefix}model_relationship_statistics (model, object_uid,relationship, attribute, recorded_at);
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

        Schema::dropIfExists("{$prefix}model_relationship_statistics");
        Schema::dropIfExists("{$prefix}model_relationship_statistic_exports");
    }
};

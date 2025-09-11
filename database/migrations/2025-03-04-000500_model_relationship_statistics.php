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

        Schema::create("{$prefix}model_relationship_statistic_exports", function (Blueprint $table) {
            $table->id();
            $table->text('model_class')->nullable(false);
            $table->text('relationship')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->timestamp('last_recorded_at')->nullable(false);
            $table->timestamps();

            $table->unique(['model_class', 'relationship', 'attribute']);
        });

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}model_relationship_statistics;
CREATE TABLE {$prefix}model_relationship_statistics (
    id BIGSERIAL,
    model_class TEXT NOT NULL,
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

CREATE UNIQUE INDEX {$prefix}model_relationship_statistics_model_object_uid_attribute_recorded_at_unique ON {$prefix}model_relationship_statistics (model_class, object_uid,relationship, attribute, recorded_at);
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

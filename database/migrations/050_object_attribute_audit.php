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
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}object_attributes_audit;
CREATE TABLE {$prefix}object_attributes_audit (
    id BIGSERIAL,
    model TEXT NOT NULL,
    object_uid TEXT NOT NULL,
    attribute TEXT NOT NULL,
    value_old TEXT NULL,
    value_new TEXT NULL,
    timestamp TIMESTAMPTZ DEFAULT NOW() NOT NULL
) PARTITION BY RANGE (timestamp);
CREATE INDEX {$prefix}object_attributes_audit_timestamp_idx  ON {$prefix}object_attributes_audit (timestamp);
CREATE INDEX {$prefix}object_attributes_audit_model_object_uid_attribute_idx  ON {$prefix}object_attributes_audit (model, object_uid, attribute);
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

        Schema::dropIfExists("{$prefix}object_attributes_audit");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('stickle.database.tablePrefix') ?? '';

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
        $prefix = config('stickle.database.tablePrefix') ?? '';

        Schema::dropIfExists("{$prefix}object_attributes_audit");
    }
};

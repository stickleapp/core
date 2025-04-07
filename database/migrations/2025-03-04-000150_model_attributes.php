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

<<<<<<< HEAD:database/migrations/2025-03-04-000150_model_attributes.php
        // model attributes
        Schema::create(("{$prefix}model_attributes"), function (Blueprint $table) {
=======
        // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
>>>>>>> 4a8290cedd927491a35310724eae633096ca9bd6:database/migrations/2025-03-04-000150_object_attributes.php
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->jsonb('model_attributes')->nullable(false);
            $table->timestamp('synced_at')->nullable(true);
            $table->timestamps();

            $table->unique(['model', 'object_uid']);

            $table->index('model');
            $table->index('object_uid');
        });

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}model_attribute_audit;
CREATE TABLE {$prefix}model_attribute_audit (
    id BIGSERIAL,
    model TEXT NOT NULL,
    object_uid TEXT NOT NULL,
    attribute TEXT NOT NULL,
    value_old TEXT NULL,
    value_new TEXT NULL,
    timestamp TIMESTAMPTZ DEFAULT NOW() NOT NULL
) PARTITION BY RANGE (timestamp);
CREATE INDEX {$prefix}model_attribute_audit_timestamp_idx  ON {$prefix}model_attribute_audit (timestamp);
CREATE INDEX {$prefix}model_attribute_audit_model_object_uid_attribute_idx  ON {$prefix}model_attribute_audit (model, object_uid, attribute);
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

<<<<<<< HEAD:database/migrations/2025-03-04-000150_model_attributes.php
        Schema::dropIfExists("{$prefix}model_attribute_audit");
        Schema::dropIfExists("{$prefix}model_attributes");
=======
        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}object_attributes");
>>>>>>> 4a8290cedd927491a35310724eae633096ca9bd6:database/migrations/2025-03-04-000150_object_attributes.php
    }
};

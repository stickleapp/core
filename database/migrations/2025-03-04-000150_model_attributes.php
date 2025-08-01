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

        // model attributes
        Schema::create(("{$prefix}model_attributes"), function (Blueprint $table) {
            $table->id();
            $table->text('model_class')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->jsonb('data')->nullable(false);
            $table->timestamp('synced_at')->nullable(true);
            $table->timestamps();

            $table->unique(['model_class', 'object_uid']);

            $table->index('model_class');
            $table->index('object_uid');
        });

        \DB::connection()->getPdo()->exec("
DROP TABLE IF EXISTS {$prefix}model_attribute_audit;
CREATE TABLE {$prefix}model_attribute_audit (
    id BIGSERIAL,
    model_class TEXT NOT NULL,
    object_uid TEXT NOT NULL,
    attribute TEXT NOT NULL,
    value_old TEXT NULL,
    value_new TEXT NULL,
    timestamp TIMESTAMPTZ DEFAULT NOW() NOT NULL
) PARTITION BY RANGE (timestamp);
CREATE INDEX {$prefix}model_attribute_audit_timestamp_idx  ON {$prefix}model_attribute_audit (timestamp);
CREATE INDEX {$prefix}model_attribute_audit_model_object_uid_attribute_idx  ON {$prefix}model_attribute_audit (model_class, object_uid, attribute);
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

        Schema::dropIfExists("{$prefix}model_attribute_audit");
        Schema::dropIfExists("{$prefix}model_attributes");
    }
};

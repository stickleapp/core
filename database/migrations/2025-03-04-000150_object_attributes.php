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

       // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
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
        Schema::dropIfExists("{$prefix}object_attributes");        
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        // model attributes
        Schema::create(("{$prefix}model_attributes"), function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->text('model_class')->nullable(false);
            $blueprint->text('object_uid')->nullable(false);
            $blueprint->jsonb('data')->nullable(false);
            $blueprint->timestamp('synced_at')->nullable(true);
            $blueprint->timestamps();

            $blueprint->unique(['model_class', 'object_uid']);

            $blueprint->index('model_class');
            $blueprint->index('object_uid');
        });

        DB::connection()->getPdo()->exec("
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
     */
    public function down(): void
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::dropIfExists("{$prefix}model_attribute_audit");
        Schema::dropIfExists("{$prefix}model_attributes");
    }
};

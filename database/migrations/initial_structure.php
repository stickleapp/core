<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string
     */
    private $prefix = 'lc_';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
        Schema::dropIfExists("{$prefix}object_segment_audit");
        Schema::dropIfExists("{$prefix}object_segment");
        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}requests");
        Schema::dropIfExists("{$prefix}object_attributes");
        Schema::dropIfExists("{$prefix}events");
        Schema::dropIfExists("{$prefix}events_rollup_1min");

        // events
        Schema::create("{$prefix}events", function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->text('object_uid')->nullable(false);
            $table->text('model')->nullable(false);
            $table->text('event_name')->nullable(false);
            $table->jsonb('properties')->nullable(true);
            $table->timestamp('timestamp')->nullable(false);
        });

        // events_rollup_1min
        \DB::connection()->getPdo()->exec("
	        CREATE TABLE {$prefix}events_rollup_1min (
                object_uid TEXT NOT NULL,
                model TEXT NOT NULL,
                event_name TEXT NOT NULL,
                minute TIMESTAMPTZ NOT NULL,
                event_count bigint
            ) PARTITION BY RANGE (minute);
	        CREATE INDEX ON {$prefix}events_rollup_1min (minute);
	        CREATE UNIQUE INDEX {$prefix}events_rollup_1min_unique_idx ON {$prefix}events_rollup_1min(object_uid, model, event_name, minute);
        ");

        // events_rollup_5min
        \DB::connection()->getPdo()->exec("
	        CREATE TABLE {$prefix}events_rollup_5min (
                object_uid TEXT NOT NULL,
                model TEXT NOT NULL,
                event_name TEXT NOT NULL,
                minute TIMESTAMPTZ NOT NULL,
                event_count bigint
            ) PARTITION BY RANGE (minute);
	        CREATE INDEX ON {$prefix}events_rollup_5min (minute);
	        CREATE UNIQUE INDEX {$prefix}events_rollup_5min_unique_idx ON {$prefix}events_rollup_5min(object_uid, model, event_name, minute);
        ");

        // events_rollup_1hr
        \DB::connection()->getPdo()->exec("
	        CREATE TABLE {$prefix}events_rollup_1hr (
                object_uid TEXT NOT NULL,
                model TEXT NOT NULL,
                event_name TEXT NOT NULL,
                hour TIMESTAMPTZ NOT NULL,
                event_count bigint
            ) PARTITION BY RANGE (hour);
	        CREATE INDEX ON {$prefix}events_rollup_1hr (hour);
	        CREATE UNIQUE INDEX {$prefix}events_rollup_1hr_unique_idx ON {$prefix}events_rollup_1hr(object_uid, model, event_name, hour);
        ");

        // events_rollup_1day
        \DB::connection()->getPdo()->exec("
	        CREATE TABLE {$prefix}events_rollup_1day (
                object_uid TEXT NOT NULL,
                model TEXT NOT NULL,
                event_name TEXT NOT NULL,
                day TIMESTAMPTZ NOT NULL,
                event_count bigint
            ) PARTITION BY RANGE (day);
	        CREATE INDEX ON {$prefix}events_rollup_1day (day);
	        CREATE UNIQUE INDEX {$prefix}events_rollup_1day_unique_idx ON {$prefix}events_rollup_1day(object_uid, model, event_name, day);
        ");

        // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->jsonb('attributes')->nullable(false);
            $table->timestamps();
        });

        // Model::join('object_attributes', 'object_uid', 'object_uid')
        //     ->where('model', 'user')
        //     ->where('attributes->age', '>', 18)
        //     ->get();

        Schema::create("{$prefix}requests", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->text('model')->nullable(false);
            $table->text('session_uid')->nullable(true);
            $table->text('url')->nullable(true);
            $table->text('path')->nullable(true);
            $table->text('host')->nullable(true);
            $table->text('search')->nullable(true);
            $table->text('query_params')->nullable(true);
            $table->text('utm_source')->nullable(true);
            $table->text('utm_medium')->nullable(true);
            $table->text('utm_campaign')->nullable(true);
            $table->text('utm_content')->nullable(true);
            $table->timestamps();
        });

        // rollups

        Schema::create("{$prefix}object_attributes_audit", function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('attribute_name')->nullable(false);
            $table->text('attribute_value')->nullable(true);
            $table->date(('attribute_updated_at'))->nullable(false);
            $table->timestamps();

            $table->unique(['model', 'object_uid', 'attribute_name', 'attribute_updated_at']);
            // ON DUPLICATE KEY UPDATE
        });

        Schema::create("{$prefix}object_segment", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text("{$prefix}segment_id")->nullable(false);
            $table->timestamps();
        });

        Schema::create("{$prefix}object_segment_audit", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->text("{$prefix}segment_id")->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
        });

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger("{$prefix}segment_group_id")->nullable(true);
            $table->text('model')->nullable(false);
            $table->jsonb('definition')->nullable(true);
            $table->integer('sort_order')->nullable(false)->default(0);
            $table->timestamps();

            $table->foreign("{$prefix}segment_group_id")->references('id')->on("{$prefix}segment_groups");
            $table->index("{$prefix}segment_group_id");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = $this->prefix;

        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
        Schema::dropIfExists("{$prefix}object_segment_audit");
        Schema::dropIfExists("{$prefix}object_segment");
        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}requests");
        Schema::dropIfExists("{$prefix}object_attributes");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_5min CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1hr CASCADE");
        DB::unprepared("DROP TABLE IF EXISTS {$prefix}events_rollup_1day CASCADE");
        Schema::dropIfExists("{$prefix}events");
    }
};

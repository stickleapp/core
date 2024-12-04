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

        // + rollups
        Schema::create("{$prefix}events", function (Blueprint $table) {
            $table->id();
            $table->string('object_uid')->nullable(false);
            $table->string('model')->nullable(false);
            $table->string('session_uid')->nullable(true);
            $table->string('event_name')->nullable(false);
            $table->jsonb('properties')->nullable(true);
            $table->jsonb('page_properties')->nullable(true);
            $table->timestamps();
        });

        // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
            $table->id();
            $table->string('model')->nullable(false);
            $table->string('object_uid')->nullable(false);
            $table->jsonb('attributes')->nullable(false);
            $table->timestamps();
        });

        // Model::join('object_attributes', 'object_uid', 'object_uid')
        //     ->where('model', 'user')
        //     ->where('attributes->age', '>', 18)
        //     ->get();

        Schema::create("{$prefix}requests", function (Blueprint $table) {
            $table->id();
            $table->string('object_uid')->nullable(false);
            $table->string('model')->nullable(false);
            $table->string('session_uid')->nullable(true);
            $table->string('url')->nullable(true);
            $table->string('path')->nullable(true);
            $table->string('host')->nullable(true);
            $table->string('search')->nullable(true);
            $table->string('query_params')->nullable(true);
            $table->string('utm_source')->nullable(true);
            $table->string('utm_medium')->nullable(true);
            $table->string('utm_campaign')->nullable(true);
            $table->string('utm_content')->nullable(true);
            $table->timestamps();
        });

        // rollups

        Schema::create("{$prefix}object_attributes_audit", function (Blueprint $table) {
            $table->id();
            $table->string('model')->nullable(false);
            $table->string('object_uid')->nullable(false);
            $table->string('attribute_name')->nullable(false);
            $table->string('attribute_value')->nullable(true);
            $table->date(('attribute_updated_at'))->nullable(false);
            $table->timestamps();

            $table->unique(['model', 'object_uid', 'attribute_name', 'attribute_updated_at']);
            // ON DUPLICATE KEY UPDATE
        });

        Schema::create("{$prefix}object_segment", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->string('model')->nullable(false);
            $table->string('object_uid')->nullable(false);
            $table->string("{$prefix}segment_id")->nullable(false);
            $table->timestamps();
        });

        Schema::create("{$prefix}object_segment_audit", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->string("{$prefix}segment_id")->nullable(false);
            $table->string('object_uid')->nullable(false);
            $table->string('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
        });

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger("{$prefix}segment_group_id")->nullable(true);
            $table->string('model')->nullable(false);
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
        Schema::dropIfExists("{$prefix}events");
    }
};

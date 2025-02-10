<?php

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //     protected string $prefix;

    // public function __construct(
    //     ?string $prefix = null
    // ) {
    //     $this->prefix = $prefix ?? '';
    // }

    // public function __construct(
    //     #[Config('stickle.database.tablePrefix')] protected ?string $prefix = null,
    // ) {}

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('stickle.database.tablePrefix') ?? '';

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

        Schema::create("{$prefix}object_attributes_audit", function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->text('from')->nullable(true);
            $table->text('to')->nullable(true);
            $table->timestamps();

            $table->unique(['model', 'object_uid', 'attribute', 'created_at']);

            $table->index('model');
            $table->index('object_uid');
            $table->index('attribute');
        });

        Schema::create("{$prefix}object_segment", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamps();

            $table->unique(['object_uid', 'segment_id']);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        Schema::create("{$prefix}object_segment_audit", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
            $table->timestamp('event_processed_at')->nullable(true);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        Schema::create("{$prefix}object_segment_statistics", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamp('first_enter_recorded_at')->nullable(true);
            $table->timestamp('first_exit_recorded_at')->nullable(true);
            $table->timestamp('last_enter_recorded_at')->nullable(true);
            $table->timestamp('last_exit_recorded_at')->nullable(true);
            $table->timestamps();

            $table->unique(['object_uid', 'segment_id']);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger('segment_group_id')->nullable(true);
            $table->text('model')->nullable(false);
            $table->text('as_class')->nullable(true);
            $table->jsonb('as_json')->nullable(true);
            $table->integer('sort_order')->nullable(false)->default(0);
            $table->integer('export_interval')->nullable(false)->default(360);
            $table->timestamp('last_exported_at')->nullable(true);
            $table->timestamps();

            $table->unique(['model', 'as_class']);
            $table->foreign('segment_group_id')->references('id')->on("{$prefix}segment_groups");
            $table->index('segment_group_id');
        });

        Schema::create("{$prefix}segment_statistic_exports", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->timestamp('last_recorded_at')->nullable(false);
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on("{$prefix}segments");
            $table->unique(['segment_id', 'attribute']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('stickle.database.tablePrefix') ?? '';

        Schema::dropIfExists("{$prefix}segment_statistic_exports");
        Schema::dropIfExists("{$prefix}segment_statistics");
        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
        Schema::dropIfExists("{$prefix}object_segment_statistics");
        Schema::dropIfExists("{$prefix}object_segment_audit");
        Schema::dropIfExists("{$prefix}object_segment");
        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}object_attributes");
    }
};

<?php

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        $this->prefix = config('cascade.database.tablePrefix');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        // object attributes
        Schema::create(("{$prefix}object_attributes"), function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->jsonb('model_attributes')->nullable(false);
            $table->timestamp('synced_at')->nullable(true);
            $table->timestamps();

            $table->unique(['model', 'object_uid']);
        });

        // Model::join("{$prefix}object_attributes", function ($join) {
        //      $join->on("{$prefix}object_attributes.object_uuid", 'object_uid')
        //      $join->where("{$prefix}object_attributes.model", $this->attributable);
        // })
        //     ->where('attributes->age', '>', 18)
        //     ->get();

        Schema::create("{$prefix}object_attributes_audit", function (Blueprint $table) {
            $table->id();
            $table->text('model')->nullable(false);
            $table->text('object_uid')->nullable(false);
            $table->text('attribute')->nullable(false);
            $table->text('from')->nullable(true);
            $table->text('to')->nullable(true);
            $table->timestamps();

            $table->unique(['model', 'object_uid', 'attribute', 'created_at']);
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

        Schema::dropIfExists("{$prefix}object_attributes_audit");
        Schema::dropIfExists("{$prefix}object_attributes");
    }
};

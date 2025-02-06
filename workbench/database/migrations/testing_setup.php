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

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(true);
            $table->text('name')->nullable(false);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('customers');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('user_rating')->nullable(true);
            $table->unsignedBigInteger('customer_id')->nullable(true);

            $table->foreign('customer_id')->references('id')->on('customers');
        });

        // Schema::table('customr_user', function (Blueprint $table) {
        //     $table->unsignedBigInteger('customer_id')->nullable(false);
        //     $table->unsignedBigInteger('user_id')->nullable(false);
        //     $table->timestamps();

        //     $table->foreign('order_id')->references('id')->on('orders');
        //     $table->foreign('customer_id')->references('id')->on('customers');
        // });

        Schema::create(('orders'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('status')->nullable(false);
            $table->datetime('order_date');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create(('order_items'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->text('item_name')->nullable(false);
            $table->text('item_number')->nullable(false);
            $table->integer('quantity')->nullable(false);
            $table->integer('price_cents')->nullable(false);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};

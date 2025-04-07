<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Model::unsetEventDispatcher();

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(true);
            $table->text('name')->nullable(false);

            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('customers');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(true);
            $table->string('user_type')->nullable(false);
            $table->integer('user_level')->nullable(false)->default(0);
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        // Schema::table('customer_user', function (Blueprint $table) {
        //     $table->unsignedBigInteger('customer_id')->nullable(false);
        //     $table->unsignedBigInteger('user_id')->nullable(false);
        //     $table->timestamps();

        //     $table->foreign('order_id')->references('id')->on('orders');
        //     $table->foreign('customer_id')->references('id')->on('customers');
        // });

        Schema::create(('tickets'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->text('title')->nullable(false);
            $table->text('description')->nullable(true);
            $table->text('status')->nullable(true);
            $table->integer('rating')->nullable(true);
            $table->text('priority')->nullable(true);
            $table->bigInteger('assigned_to_id')->nullable(true);
            $table->bigInteger('created_by_id')->nullable(false);
            $table->bigInteger('resolved_by_id')->nullable(true);
            $table->timestamp('resolved_at')->nullable(true);
            $table->integer('resolved_in_seconds')->storedAs(
                'EXTRACT(EPOCH FROM (resolved_at - created_at))::integer'
            )->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('assigned_to_id')->references('id')->on('users');
            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('resolved_by_id')->references('id')->on('users');
        });

        // Schema::table(('tickets'), function (Blueprint $table) {});

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_items');

        Schema::dropIfExists('subscriptions');

        Schema::dropIfExists('tickets');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'customer_id', 'user_type',
            ]);
        });
        Schema::dropIfExists('customers');
    }
};

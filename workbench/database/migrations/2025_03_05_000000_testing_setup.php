<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Model::unsetEventDispatcher();

        Schema::create('customers', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('parent_id')->nullable(true);
            $blueprint->text('name')->nullable(false);

            $blueprint->string('stripe_id')->nullable()->index();
            $blueprint->string('pm_type')->nullable();
            $blueprint->string('pm_last_four', 4)->nullable();
            $blueprint->timestamp('trial_ends_at')->nullable();

            $blueprint->timestamps();

            $blueprint->foreign('parent_id')->references('id')->on('customers');
        });

        Schema::table('users', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('users', 'customer_id')) {
                $blueprint->unsignedBigInteger('customer_id')->nullable(true);
                $blueprint->foreign('customer_id')->references('id')->on('customers');
            }
            if (! Schema::hasColumn('users', 'user_type')) {
                $blueprint->string('user_type')->nullable(false)->default('End User');
            }
            if (! Schema::hasColumn('users', 'user_level')) {
                $blueprint->integer('user_level')->nullable(false)->default(0);
            }
        });

        // Schema::table('customer_user', function (Blueprint $table) {
        //     $table->unsignedBigInteger('customer_id')->nullable(false);
        //     $table->unsignedBigInteger('user_id')->nullable(false);
        //     $table->timestamps();

        //     $table->foreign('order_id')->references('id')->on('orders');
        //     $table->foreign('customer_id')->references('id')->on('customers');
        // });

        Schema::create(('tickets'), function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('customer_id');
            $blueprint->text('title')->nullable(false);
            $blueprint->text('description')->nullable(true);
            $blueprint->text('status')->nullable(true);
            $blueprint->integer('rating')->nullable(true);
            $blueprint->text('priority')->nullable(true);
            $blueprint->bigInteger('assigned_to_id')->nullable(true);
            $blueprint->bigInteger('created_by_id')->nullable(false);
            $blueprint->bigInteger('resolved_by_id')->nullable(true);
            $blueprint->timestamp('resolved_at')->nullable(true);
            $blueprint->integer('resolved_in_seconds')->storedAs(
                'EXTRACT(EPOCH FROM (resolved_at - created_at))::integer'
            )->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('customer_id')->references('id')->on('customers');
            $blueprint->foreign('assigned_to_id')->references('id')->on('users');
            $blueprint->foreign('created_by_id')->references('id')->on('users');
            $blueprint->foreign('resolved_by_id')->references('id')->on('users');
        });

        // Schema::table(('tickets'), function (Blueprint $table) {});

        Schema::create('subscriptions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('customer_id');
            $blueprint->string('type');
            $blueprint->string('stripe_id')->unique();
            $blueprint->string('stripe_status');
            $blueprint->string('stripe_price')->nullable();
            $blueprint->integer('quantity')->nullable();
            $blueprint->timestamp('trial_ends_at')->nullable();
            $blueprint->timestamp('ends_at')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['customer_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('subscription_id');
            $blueprint->string('stripe_id')->unique();
            $blueprint->string('stripe_product');
            $blueprint->string('stripe_price');
            $blueprint->integer('quantity')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['subscription_id', 'stripe_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_items');

        Schema::dropIfExists('subscriptions');

        Schema::dropIfExists('tickets');

        Schema::table('users', function (Blueprint $blueprint): void {
            $blueprint->dropColumn([
                'customer_id', 'user_type', 'user_level',
            ]);
        });
        Schema::dropIfExists('customers');
    }
};

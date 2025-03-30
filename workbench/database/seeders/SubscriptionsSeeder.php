<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Subscription;
use Workbench\App\Models\SubscriptionItem;

class SubscriptionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('subscriptions')->truncate();

        $customers = Customer::has('users')->get();

        foreach ($customers as $customer) {
            $subscription = Subscription::factory()
                ->has(SubscriptionItem::factory()->count(1))
                ->create(
                    [
                        'customer_id' => $customer->id,
                    ]
                );
        }
    }
}

<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Order;
use Workbench\Database\Factories\OrderItemFactory;

class OrdersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();

        $customers = DB::table('customers')->get();

        foreach ($customers as $customer) {
            Order::factory()
                ->count(rand(1, 5))
                ->has(OrderItemFactory::new()->count(rand(1, 5)))
                // ->for($user) // don't know why this one doesn't work
                ->create(
                    [
                        'customer_id' => $customer->id,
                    ]
                );
        }
    }
}

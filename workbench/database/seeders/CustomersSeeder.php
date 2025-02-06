<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;

class CustomersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('customers')->truncate();

        $customers = Customer::factory()
            ->count(1000)
            ->createQuietly();

        // Add some children to the customers
        $customers->take(100)->each(function ($customer) {
            Customer::factory()
                ->count(3)
                ->createQuietly(
                    [
                        'parent_id' => $customer->id,
                    ]
                );
        });
    }
}

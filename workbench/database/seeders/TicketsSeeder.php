<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Ticket;

class TicketsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('tickets')->truncate();

        $customers = Customer::has('users')->get()->take(500);

        foreach ($customers as $customer) {
            $batchCount = rand(1, 50);
            for ($i = 0; $i < $batchCount; $i++) {
                $createdAt = new Carbon(fake()->dateTimeBetween($customer->created_at, now()));
                $resolvedAt = fake()->optional(.9)->dateTimeBetween($createdAt, (clone $createdAt)->addMinutes(rand(10, 2000)));
                Ticket::factory()
                    ->create(
                        [
                            'customer_id' => $customer->id,
                            'created_by_id' => $customer->endUsers->random()->id,
                            'assigned_to_id' => $customer->agents->random()->id,
                            'created_at' => $createdAt,
                            'resolved_at' => $resolvedAt,
                            'rating' => $resolvedAt ? fake()->numberBetween(1, 5) : null,
                        ]
                    );
            }
        }
    }
}

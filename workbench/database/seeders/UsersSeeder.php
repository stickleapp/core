<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Enums\UserType;
use Workbench\App\Models\Customer;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class UsersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->truncate();

        UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'user_type' => UserType::ADMIN,
        ]);

        // Add some children to the customers
        $customers = Customer::all();
        $customers->each(function ($customer): void {
            User::factory()
                ->count(random_int(1, 5))
                ->createQuietly(
                    [
                        'customer_id' => $customer->id,
                        'user_type' => UserType::AGENT,
                        'created_at' => fake()->dateTimeBetween($customer->created_at, now()),
                    ]
                );
            User::factory()
                ->count(random_int(1, 25))
                ->createQuietly(
                    [
                        'customer_id' => $customer->id,
                        'user_type' => UserType::END_USER,
                        'created_at' => fake()->dateTimeBetween($customer->created_at, now()),
                    ]
                );
        });
    }
}

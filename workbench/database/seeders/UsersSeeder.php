<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        ]);

        // Add some children to the customers
        Customer::all()->take(950)->each(function ($customer) {
            User::factory()
                ->count(5)
                ->createQuietly(
                    [
                        'customer_id' => $customer->id,
                        'user_rating' => rand(1, 3),
                    ]
                );
        });
    }
}

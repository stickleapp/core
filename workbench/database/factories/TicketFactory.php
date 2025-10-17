<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Ticket;
use Workbench\App\Models\User;

/**
 * @template TModel of \Workbench\App\Models\Ticket
 *
 * @extends Factory<TModel>
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a customer with at least one user
        $customer = Customer::factory()
            ->has(User::factory()->count(2))
            ->create();

        return [
            'status' => ['open', 'pending', 'in-progress', 'resolved'][fake()->numberBetween(0, 3)],
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => ['low', 'medium', 'high'][fake()->numberBetween(0, 2)],
            'assigned_to_id' => $customer->users()->inRandomOrder()->first()->id,
            'created_by_id' => $customer->users()->inRandomOrder()->first()->id,
        ];
    }
}

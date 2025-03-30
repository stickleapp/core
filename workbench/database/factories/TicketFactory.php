<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Ticket;

/**
 * @template TModel of \Workbench\App\Models\Ticket
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
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
        // Get a random customer that has at least one user
        $customer = Customer::has('users')->inRandomOrder()->first();

        return [
            'status' => ['open', 'pending', 'in-progressed', 'resolved'][fake()->numberBetween(0, 3)],
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => ['low', 'medium', 'high'][fake()->numberBetween(0, 2)],
            'assigned_to_id' => $customer->users()->inRandomOrder()->first()->id,
            'created_by_id' => $customer->users()->inRandomOrder()->first()->id,
        ];
    }
}

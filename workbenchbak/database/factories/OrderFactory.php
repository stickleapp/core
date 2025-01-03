<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Order;

/**
 * @template TModel of \Workbench\App\Models\Order
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => ['pending', 'processing', 'shipped', 'delivered'][fake()->numberBetween(0, 3)],
            'order_date' => fake()->dateTime(),
        ];
    }
}

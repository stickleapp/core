<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\OrderItem;

/**
 * @template TModel of \Workbench\App\Models\Order
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_name' => fake()->word(),
            'item_number' => fake()->uuid(),
            'quantity' => fake()->numberBetween(1, 10),
            'price_cents' => fake()->numberBetween(100, 15000),
        ];
    }
}

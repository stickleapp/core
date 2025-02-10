<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Customer;

/**
 * @template TModel of \Workbench\App\Models\Customer
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            // 'mrr' => [24, 49, 99, 199, 499, 999][rand(0, 5)],
        ];
    }
}

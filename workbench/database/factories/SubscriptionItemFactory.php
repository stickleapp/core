<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\SubscriptionItem;

/**
 * @template TModel of \Workbench\App\Models\Subscription
 *
 * @extends Factory<TModel>
 */
class SubscriptionItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = SubscriptionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_id' => 'si_'.fake()->uuid(),
            'stripe_product' => 'prod_'.fake()->uuid(),
            'stripe_price' => 'price_'.fake()->uuid(),
            'quantity' => 1,
        ];
    }
}

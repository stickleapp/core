<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Subscription;

/**
 * @template TModel of \Workbench\App\Models\Subscription
 *
 * @extends Factory<TModel>
 */
class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = ['active', 'incomplete', 'canceled', 'trialing'][fake()->numberBetween(0, 3)];

        switch ($status) {
            case 'active':
            case 'incomplete':
                $createdAt = now()->subDays(random_int(1, 1000));
                $trialEndsAt = null;
                $endsAt = null;
                break;
            case 'canceled':
                $createdAt = now()->subDays(random_int(1, 1000));
                $endsAt = fake()->dateTimeBetween($createdAt, now());
                $trialEndsAt = null;
                break;
            case 'trialing':
                $status = 'trialing';
                $createdAt = now()->subDays(random_int(1, 30));
                $trialEndsAt = $createdAt->addDays(30);
                $endsAt = null;
                break;
        }

        return [
            'type' => 'default',
            'stripe_id' => 'sub_'.fake()->uuid(),
            'stripe_status' => $status,
            'stripe_price' => 'price_'.fake()->uuid(),
            'quantity' => 1,
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => $endsAt,
            'created_at' => $createdAt,
        ];
    }
}

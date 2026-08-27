<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;
use Workbench\App\Models\User;

/**
 * The aggregate reads the *related* model's data column, so the work list has
 * to come from the related model's tracked attributes. Built from the parent's
 * instead, a parent that tracks nothing produces no rows at all, and a parent
 * that tracks a different name aggregates a key its children do not have.
 *
 * Workbench Customer does not track user_rating; workbench User does. That is
 * the shape docs/guide/tracking-attributes.md documents under "Parent-Child
 * Aggregation".
 */
test('a parent that tracks nothing still aggregates its children tracked attributes', function (): void {
    $customer = Customer::query()->create(['name' => 'Acme']);

    $first = User::factory()->create(['customer_id' => $customer->id]);
    $second = User::factory()->create(['customer_id' => $customer->id]);

    $first->trackable_attributes = ['user_rating' => 2];
    $second->trackable_attributes = ['user_rating' => 4];

    // The point of the fix: user_rating belongs to the child, and the parent
    // aggregates it without tracking it itself.
    expect(Customer::stickleTrackedAttributes())->not->toContain('user_rating');
    expect(User::stickleTrackedAttributes())->toContain('user_rating');

    $this->artisan('stickle:record-model-relationship-statistics')->assertSuccessful();

    $row = DB::table(config('stickle.database.tablePrefix').'model_relationship_statistics')
        ->where('object_uid', (string) $customer->id)
        ->where('relationship', 'users')
        ->where('attribute', 'user_rating')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->value_avg)->toBe(3.0)
        ->and($row->value_count)->toBe(2.0)
        ->and($row->model_class)->toBe('Customer');
});

/**
 * The access pattern docs/guide/tracking-attributes.md documents under
 * "Parent-Child Aggregation". modelRelationshipStatistics() matches on the
 * stored basename, so it reads nothing unless the writer stores the same.
 */
test('the documented accessor reads the aggregate back', function (): void {
    $customer = Customer::query()->create(['name' => 'Acme']);

    $first = User::factory()->create(['customer_id' => $customer->id]);
    $second = User::factory()->create(['customer_id' => $customer->id]);

    $first->trackable_attributes = ['user_rating' => 1];
    $second->trackable_attributes = ['user_rating' => 5];

    $this->artisan('stickle:record-model-relationship-statistics')->assertSuccessful();

    $statistic = $customer->modelRelationshipStatistics()
        ->where('relationship', 'users')
        ->where('attribute', 'user_rating')
        ->first();

    expect($statistic)->not->toBeNull()
        ->and($statistic->value_avg)->toBe(3.0)
        ->and($statistic->value_min)->toBe(1.0)
        ->and($statistic->value_max)->toBe(5.0)
        ->and($statistic->value_sum)->toBe(6.0);
});

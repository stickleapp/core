<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Customer;
use Workbench\App\Models\User;

/**
 * The rollup casts every value to ::float. That held only while every tracked
 * attribute happened to be numeric -- nothing in the package states or enforces
 * that, and DataType::STRING metadata never reaches the SQL.
 *
 * A string attribute raises SQLSTATE[22P02] mid-run. Because the export marker
 * is only written after the aggregate succeeds, the five-minute command then
 * redispatches the same failing work forever.
 */
test('a string tracked attribute does not break the relationship rollup', function (): void {
    $customer = Customer::factory()->create();

    $first = User::factory()->create(['customer_id' => $customer->id]);
    $second = User::factory()->create(['customer_id' => $customer->id]);

    $first->trackable_attributes = ['user_rating' => 2, 'plan_name' => 'starter'];
    $second->trackable_attributes = ['user_rating' => 4, 'plan_name' => 'enterprise'];

    $this->artisan('stickle:record-model-relationship-statistics')->assertSuccessful();

    $rows = DB::table(config('stickle.database.tablePrefix').'model_relationship_statistics')
        ->where('object_uid', (string) $customer->id)
        ->where('relationship', 'users')
        ->get()
        ->keyBy('attribute');

    // The numeric attribute still aggregates correctly.
    expect((float) $rows['user_rating']->value_avg)->toBe(3.0);

    // The string attribute is counted, and its numeric aggregates are null
    // rather than an error.
    expect($rows)->toHaveKey('plan_name')
        ->and((int) $rows['plan_name']->value_count)->toBe(2)
        ->and($rows['plan_name']->value_avg)->toBeNull();
});

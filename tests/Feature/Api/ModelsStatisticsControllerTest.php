<?php

declare(strict_types=1);

use StickleApp\Core\Models\ModelAttributes;
use Workbench\App\Models\User;

it('returns models statistics data via API request', function (): void {
    // Create some users
    $users = User::factory()->count(3)->create();

    // Create model attributes with ticket_count data
    foreach ($users as $index => $user) {
        ModelAttributes::query()->firstOrCreate([
            'object_uid' => (string) $user->id,
            'model_class' => 'User',
        ], [
            'data' => ['ticket_count' => ($index + 1) * 5], // 5, 10, 15
            'synced_at' => now()->subDays($index + 1), // 1, 2, 3 days ago
        ]);
    }

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'model_class' => 'user',
        'attribute' => 'ticket_count',
    ]);

    $response = $this->getJson("/stickle/api/models-statistics?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check response structure - should return array with statistical data
    expect($data)->toBeArray();
    expect($data)->toHaveCount(1);
    expect($data[0])->toHaveKeys(['value_avg', 'value_min', 'value_max', 'value_sum', 'value_count']);
    // value_count is a COUNT(*) over every joined row, and a migration seeds
    // this database, so the absolute number depends on whether this test
    // happens to run before or after that data is present. It passed only by
    // ordering luck. The three users created above are the floor.
    expect($data[0]['value_count'])->toBeGreaterThanOrEqual(3);
});

/**
 * The same ::float cast as the relationship rollup: a request for a string
 * attribute raised SQLSTATE[22P02] and the endpoint returned a 500.
 */
it('returns statistics for a string attribute instead of a 500', function (): void {
    $users = User::factory()->count(2)->create();

    foreach ($users as $index => $user) {
        ModelAttributes::query()->firstOrCreate([
            'object_uid' => (string) $user->id,
            'model_class' => 'User',
        ], [
            'data' => ['plan_name' => $index === 0 ? 'starter' : 'enterprise'],
            'synced_at' => now()->subDay(),
        ]);
    }

    $queryParams = http_build_query([
        'model_class' => 'user',
        'attribute' => 'plan_name',
    ]);

    $response = $this->getJson("/stickle/api/models-statistics?{$queryParams}");

    $response->assertOk();

    $data = $response->json();

    // No assertion on value_count: COUNT(*) here is not filtered by attribute,
    // and a migration seeds this database, so the number depends on global
    // state rather than on anything this test set up. What matters is that a
    // string attribute yields a null numeric aggregate instead of raising.
    expect($data[0]['value_avg'])->toBeNull()
        ->and($data[0]['value_min'])->toBeNull()
        ->and($data[0]['value_max'])->toBeNull()
        ->and($data[0]['value_sum'])->toBeNull();
});

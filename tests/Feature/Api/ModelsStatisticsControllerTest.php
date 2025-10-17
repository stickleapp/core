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
    expect($data[0]['value_count'])->toBe(3);
});

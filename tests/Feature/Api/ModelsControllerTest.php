<?php

declare(strict_types=1);

use Workbench\App\Models\User;

it('returns models data via API request', function () {
    // Create some users
    $users = User::factory()->count(3)->create();

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'model_class' => 'User',
        'per_page' => 25,
    ]);

    $response = $this->getJson("/stickle/api/models?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check pagination structure
    expect($data)->toHaveKeys(['data']);
    expect($data['data'])->toHaveCount(3);
    expect($data['total'])->toBe(3);
});

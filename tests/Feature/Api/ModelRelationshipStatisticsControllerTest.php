<?php

declare(strict_types=1);

use StickleApp\Core\Models\ModelRelationshipStatistic;
use Workbench\App\Models\User;

it('returns model relationship statistics data via API request', function () {
    // Create a user
    $user = User::factory()->create();

    // Create some model relationship statistics
    ModelRelationshipStatistic::create([
        'model_class' => 'User',
        'object_uid' => (string) $user->id,
        'attribute' => 'ticket_count',
        'relationship' => 'ticketsAssigned',
        'value' => 5,
        'value_count' => 1,
        'value_sum' => 5,
        'value_min' => 5,
        'value_max' => 5,
        'value_avg' => 5.0,
        'recorded_at' => now()->subDays(7),
    ]);

    ModelRelationshipStatistic::create([
        'model_class' => 'User',
        'object_uid' => (string) $user->id,
        'attribute' => 'ticket_count',
        'relationship' => 'ticketsAssigned',
        'value' => 8,
        'value_count' => 1,
        'value_sum' => 8,
        'value_min' => 8,
        'value_max' => 8,
        'value_avg' => 8.0,
        'recorded_at' => now()->subDays(3),
    ]);

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'uid' => (string) $user->id,
        'model_class' => 'user',
        'attribute' => 'ticket_count',
        'relationship' => 'ticketsAssigned',
    ]);

    $response = $this->getJson("/stickle/api/model-relationship-statistics?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check response structure
    expect($data)->toHaveKeys(['value', 'time_series', 'delta', 'period']);
    expect($data['time_series'])->toBeArray();
    expect($data['time_series'])->toHaveCount(2);
    expect($data['delta'])->toBeArray();
    expect($data['period'])->toHaveKeys(['start', 'end']);
});

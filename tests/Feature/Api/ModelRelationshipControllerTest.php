<?php

declare(strict_types=1);

use Workbench\App\Models\Ticket;
use Workbench\App\Models\User;

it('returns model relationship data via API request', function (): void {
    // Create a user with related tickets
    $user = User::factory()->create();

    $tickets = Ticket::factory()->count(3)->create(['customer_id' => $user->customer_id, 'assigned_to_id' => $user->id]);

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'object_uid' => (string) $user->id,
        'model_class' => 'User',
        'relationship' => 'ticketsAssigned',
        'per_page' => 25,
    ]);

    $response = $this->getJson("/stickle/api/model-relationship?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check pagination structure
    expect($data)->toHaveKeys(['data']);
    expect($data['data'])->toHaveCount(3);
    expect($data['total'])->toBe(3);
});

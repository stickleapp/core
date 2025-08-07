<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\ModelAttributeAudit;
use Workbench\App\Models\User;

it('returns model attribute audit data via API request', function () {
    // Create a user with the StickleEntity trait
    $user = User::factory()->create();

    // Create some model attribute audit records
    ModelAttributeAudit::create([
        'model_class' => User::class,
        'object_uid' => (string) $user->id,
        'attribute' => 'user_rating',
        'value_old' => '3',
        'value_new' => '4',
        'timestamp' => now()->subDays(20),
    ]);

    ModelAttributeAudit::create([
        'model_class' => User::class,
        'object_uid' => (string) $user->id,
        'attribute' => 'user_rating',
        'value_old' => '4',
        'value_new' => '5',
        'timestamp' => now()->subDays(10),
    ]);

    // Mock the Log facade to verify logging
    Log::shouldReceive('debug')
        ->once()
        ->with('ModelAttributeAuditController', \Mockery::any());

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'uid' => (string) $user->id,
        'model_class' => 'User',
        'attribute' => 'user_rating',
    ]);
    
    $response = $this->getJson("/stickle/api/model-attribute-audit?{$queryParams}");

    // Assert basic response
    $response->assertOk();
    
    $data = $response->json();
    
    // Basic structure check
    expect($data)->toHaveKeys(['time_series', 'period']);
    
    // Check if delta exists when there's data
    if (isset($data['delta'])) {
        expect($data['delta'])->toHaveKeys(['start_value', 'end_value', 'absolute_change']);
    }
});

<?php

use Workbench\App\Models\User;

it('tracks attribute changes through stickle entity', function () {
    $user = User::factory()->create();

    // Set initial attribute
    $user->trackable_attributes = ['shoe_size' => 40];

    // Check current value
    expect($user->stickleAttribute('shoe_size')->current())->toBe(40);

    // Update attribute
    $user->trackable_attributes = ['shoe_size' => 41];

    // Check updated value
    expect($user->stickleAttribute('shoe_size')->current())->toBe(41);

    // Check history
    $history = $user->stickleAttribute('shoe_size')->audit()->all();
    expect($history)->toHaveCount(1); // Only contains value_new from audit records

    // Get timeline to see both old and new values
    $timeline = $user->stickleAttribute('shoe_size')->audit()->timeline();
    expect($timeline)->toHaveCount(1);
    expect($timeline[0]['value'])->toBe(41);
    expect($timeline[0]['old_value'])->toBe(40);
});

it('preserves other attributes when updating', function () {
    $user = User::factory()->create();

    // Set multiple attributes
    $user->trackable_attributes = [
        'shoe_size' => 40,
        'hair_color' => 'brown',
    ];

    // Update just one
    $user->trackable_attributes = ['shoe_size' => 41];

    // Both attributes should be preserved
    expect($user->stickleAttribute('shoe_size')->current())->toBe(41);
    expect($user->stickleAttribute('hair_color')->current())->toBe('brown');
});

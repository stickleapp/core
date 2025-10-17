<?php

use Workbench\App\Models\User;

it('tracks attribute changes through stickle entity', function (): void {
    $user = User::factory()->create();

    // Set initial attribute
    $user->trackable_attributes = ['shoe_size' => 40];

    // Check current value
    expect($user->stickleAttribute('shoe_size'))->toBe(40);

    // Update attribute
    $user->trackable_attributes = ['shoe_size' => 48];

    // These two methods do not work -- possible Laravel bug?
    $newModel = $user->fresh();
    // $user->refresh();
    // $user = User::find($user->id);

    // Check updated value
    expect($newModel->stickleAttribute('shoe_size'))->toBe(48);
});

it('preserves other attributes when updating', function (): void {
    $user = User::factory()->create();

    // Set initial attributes
    $user->trackable_attributes = ['hair_color' => 'brown'];

    // Update just one
    $user->trackable_attributes = ['shoe_size' => 41];

    // Both attributes should be preserved
    expect($user->stickleAttribute('shoe_size'))->toBe(41);
    expect($user->stickleAttribute('hair_color'))->toBe('brown');
});

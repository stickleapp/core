<?php

declare(strict_types=1);

namespace StickleEntityTest;

use StickleApp\Core\Models\ModelAttributes;
use Workbench\App\Models\User;

it('observed properties log changes', function (): void {

    $user = User::factory()->create([
        'user_level' => 1,
    ]);

    $user->user_level = 3;
    $user->save();

    $user->user_level = 5;
    $user->save();

    expect($user->modelAttributeAudits()->where('attribute', 'user_level')->count())->toBe(3);
});

it('gets current attribute value', function (): void {
    $user = User::factory()->create();

    ModelAttributes::query()->updateOrCreate([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
    ], [
        'data' => ['shoe_size' => 42],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size');

    expect($shoeSize)->toBe(42);
});

it('returns null for missing attributes', function (): void {
    $user = User::factory()->create();

    ModelAttributes::query()->firstOrCreate([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
    ], [
        'data' => ['hair_color' => 'brown'],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size');

    expect($shoeSize)->toBeNull();
});

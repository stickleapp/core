<?php

declare(strict_types=1);

namespace StickleEntityTest;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\ModelAttributes;
use Workbench\App\Models\User;

it('will expose eloquent methods', function () {
    $user = new User;
    expect(method_exists($user, 'scopestickle'))->toBeTrue();
});

it('can call stickle()', function () {
    $filter = Mockery::mock(Filter::class);
    $filter->shouldReceive('apply');
    expect(User::query()->stickle($filter))->toBeInstanceOf(Builder::class);
});

it('has observed attributes', function () {
    expect(User::getStickleObservedAttributes())->toBeArray();
});

it('has tracked attributes', function () {
    expect(User::getStickleTrackedAttributes())->toBeArray();
});

it('observed properties log changes', function () {

    $user = User::factory()->create([
        'user_level' => 1,
    ]);

    $user->user_level = 3;
    $user->save();

    $user->user_level = 5;
    $user->save();

    expect($user->modelAttributeAudits()->where('attribute', 'user_level')->count())->toBe(3);
});

it('gets current attribute value', function () {
    $user = User::factory()->create();

    ModelAttributes::updateOrCreate([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
    ], [
        'data' => ['shoe_size' => 42],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size');

    expect($shoeSize)->toBe(42);
});

it('returns null for missing attributes', function () {
    $user = User::factory()->create();

    ModelAttributes::firstOrCreate([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
    ], [
        'data' => ['hair_color' => 'brown'],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size');

    expect($shoeSize)->toBeNull();
});

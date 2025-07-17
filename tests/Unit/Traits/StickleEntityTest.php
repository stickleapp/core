<?php

declare(strict_types=1);

namespace StickleEntityTest;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\ModelAttributeAudit;
use StickleApp\Core\Models\ModelAttributes;
use StickleApp\Core\Support\StickleAttributeAccessor;
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

it('returns a stickle attribute accessor instance', function () {
    $user = User::factory()->create();

    $accessor = $user->stickleAttribute('shoe_size');

    expect($accessor)->toBeInstanceOf(StickleAttributeAccessor::class);
});

it('gets current attribute value', function () {
    $user = User::factory()->create();

    ModelAttributes::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'data' => ['shoe_size' => 42],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size')->current();

    expect($shoeSize)->toBe(42);
});

it('returns null for missing attributes', function () {
    $user = User::factory()->create();

    ModelAttributes::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'data' => ['hair_color' => 'brown'],
        'synced_at' => now(),
    ]);

    $shoeSize = $user->stickleAttribute('shoe_size')->current();

    expect($shoeSize)->toBeNull();
});

it('gets attribute history', function () {
    $user = User::factory()->create();

    // Create attribute history
    ModelAttributeAudit::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => 41,
        'value_new' => 42,
        'change_type' => 'update',
        'created_at' => now()->subDays(2),
    ]);

    ModelAttributeAudit::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => null,
        'value_new' => 41,
        'change_type' => 'create',
        'created_at' => now()->subDays(5),
    ]);

    $history = $user->stickleAttribute('shoe_size')->audit()->all();

    expect($history)->toHaveCount(2);
    expect($history[0])->toBe(42);
    expect($history[1])->toBe(41);
});

it('gets latest attribute value from history', function () {
    $user = User::factory()->create();

    // Create attribute history
    ModelAttributeAudit::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => 41,
        'value_new' => 42,
        'change_type' => 'update',
        'created_at' => now()->subDays(2),
    ]);

    ModelAttributeAudit::create([
        'model_class' => class_basename($user),
        'object_uid' => $user->getKey(),
        'attribute' => 'shoe_size',
        'value_old' => null,
        'value_new' => 41,
        'change_type' => 'create',
        'created_at' => now()->subDays(5),
    ]);

    $latest = $user->stickleAttribute('shoe_size')->latest();

    expect($latest)->toBe(42);
});

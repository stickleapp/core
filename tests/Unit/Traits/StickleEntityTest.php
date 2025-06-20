<?php

declare(strict_types=1);

namespace StickleEntityTest;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use StickleApp\Core\Filters\Base as Filter;
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

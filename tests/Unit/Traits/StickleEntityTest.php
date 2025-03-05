<?php

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
    $user = new User;
    expect($user->getObservedAttributes())->toBeArray();
});

it('observed properties log changes', function () {

    // dd([
    //     config('database.connections.pgsql.host'),
    //     config('database.connections.pgsql.username'),
    //     config('database.connections.pgsql.password'),
    //     config('database.connections.pgsql.database'),
    // ]);
    $user = User::factory()->create([
        'user_rating' => 1,
    ]);

    $user->user_rating = 3;
    $user->save();

    $user->user_rating = 5;
    $user->save();

    expect($user->objectAttributesAudits()->where('attribute', 'user_rating')->count())->toBe(3);
});

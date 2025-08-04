<?php

declare(strict_types=1);

namespace StickleEntityTest;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

it('will expose eloquent methods', function () {
    $user = new User;
    expect(method_exists($user, 'scopeStickleWhere'))->toBeTrue();
});

it('can call stickle()', function () {
    $filter = Mockery::mock(Filter::class);
    $filter->shouldReceive('apply');
    expect(User::query()->stickleWhere($filter))->toBeInstanceOf(Builder::class);
});

it('has observed attributes', function () {
    expect(User::stickleObservedAttributes())->toBeArray();
});

it('has tracked attributes', function () {
    expect(User::stickleTrackedAttributes())->toBeArray();
});

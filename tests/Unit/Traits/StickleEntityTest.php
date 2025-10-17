<?php

declare(strict_types=1);

namespace StickleEntityTest;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

it('will expose eloquent methods', function (): void {
    $user = new User;
    expect(method_exists($user, 'scopeStickleWhere'))->toBeTrue();
});

it('can call stickle()', function (): void {
    $mock = Mockery::mock(Filter::class);
    $mock->shouldReceive('apply');
    expect(User::query()->stickleWhere($mock))->toBeInstanceOf(Builder::class);
});

it('has observed attributes', function (): void {
    expect(User::stickleObservedAttributes())->toBeArray();
});

it('has tracked attributes', function (): void {
    expect(User::stickleTrackedAttributes())->toBeArray();
});

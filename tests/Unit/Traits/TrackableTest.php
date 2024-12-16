<?php

namespace TrackableTest;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Workbench\App\Models\User;

it('will expose eloquent methods', function () {
    $user = new User;
    expect(method_exists($user, 'scopeCascade'))->toBeTrue();
});

it('can call cascade()', function () {
    $filter = Mockery::mock(Filter::class);
    $filter->shouldReceive('apply');
    expect(User::query()->cascade($filter))->toBeInstanceOf(Builder::class);
});

it('has observed attributes', function () {
    $user = new User;
    expect($user->getObservableProperties())->toBeArray();
});

it('observed properties log changes', function () {

    $user = User::factory()->create();
    $user->votes = 10;
    $user->save();

    $user->votes = 15;
    $user->save();

    expect($user->getObservableProperties())->toBeArray();
});

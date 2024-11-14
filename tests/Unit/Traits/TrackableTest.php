<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Traits\Trackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Trackable;

    /**
     * Get the user's first name.
     */
    protected function numberOfHats(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => (int) $value,
        );
    }
}

it('will expose eloquent methods', function () {
    $user = new User;
    expect(method_exists($user, 'scopeCascade'))->toBeTrue();
});

it('can call cascade()', function () {
    $filter = Mockery::mock(Filter::class);
    $filter->shouldReceive('apply');
    expect(User::query()->cascade($filter))->toBeInstanceOf(Builder::class);
});

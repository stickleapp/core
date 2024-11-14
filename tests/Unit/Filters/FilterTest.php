<?php

namespace FilterTest;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Traits\Trackable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends \Illuminate\Database\Eloquent\Model
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

test('example', function () {

    $query = User::query()
        ->cascade(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )->orCascade(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )->orCascade(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->between(now()->subYears(1), now())
        );

    expect($query->toSql())->not()->toBeEmpty();
});

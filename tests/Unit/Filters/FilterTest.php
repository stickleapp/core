<?php

namespace FilterTest;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Traits\Trackable;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Trackable;
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

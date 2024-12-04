<?php

namespace EventCountTest;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Traits\Trackable;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Trackable;

    protected $table = 'users';
}

test('Uses Event Count', function () {

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

test('Uses Event Count with Delta', function () {

    $query = User::query()
        ->cascade(
            Filter::eventCount('clicked:something')
                ->increased(
                    [now()->subYears(2), now()->subYears(1)],
                    [now()->subYears(1), now()],
                )
                ->greaterThan(10)
        )->orCascade(
            Filter::eventCount('clicked:something')
                ->decreased(
                    [now()->subYears(2), now()->subYears(1)],
                    [now()->subYears(1), now()],
                )
                ->greaterThan(10)
        );

    expect($query->toSql())->not()->toBeEmpty();
});

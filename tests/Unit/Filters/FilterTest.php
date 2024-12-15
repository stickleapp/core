<?php

namespace FilterTest;

use Carbon\Carbon;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Traits\Trackable;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Trackable;

    protected $table = 'users';
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
        )->orCascade(
            Filter::datetime('a_column')
                ->occurredBefore(Carbon::now()->subYears(1))
        )->orCascade(
            Filter::datetime('a_column')
                ->isNull('a_column')
        );

    expect($query->toSql())->not()->toBeEmpty();
});

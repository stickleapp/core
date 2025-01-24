<?php

namespace FilterTest;

use Carbon\Carbon;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\\Core\Core\Traits\Trackable;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Trackable;

    protected $table = 'users';
}

test('example', function () {

    // $query = User::query()
    //     ->stickle(
    //         Filter::eventCount('clicked:something')
    //             ->greaterThan(10)
    //             ->startDate(now()->subYears(1))
    //             ->endDate(now())
    //     )->orSTICKLE(
    //         Filter::eventCount('clicked:something')
    //             ->greaterThan(10)
    //             ->startDate(now()->subYears(1))
    //             ->endDate(now())
    //     )->orSTICKLE(
    //         Filter::eventCount('clicked:something')
    //             ->greaterThan(10)
    //             ->between(now()->subYears(1), now())
    //     )->orSTICKLE(
    //         Filter::datetime('a_column')
    //             ->occurredBefore(Carbon::now()->subYears(1))
    //     )->orSTICKLE(
    //         Filter::datetime('a_column')
    //             ->isNull('a_column')
    //     );

    $query = User::query()
        ->stickle(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )
        ->orSTICKLE(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )->orSTICKLE(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )->orSTICKLE(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        )->orSTICKLE(
            Filter::eventCount('clicked:something')
                ->greaterThan(10)
                ->startDate(now()->subYears(1))
                ->endDate(now())
        );

    expect($query->toSql())->not()->toBeEmpty();
});

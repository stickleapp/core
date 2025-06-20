<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

use function Pest\Faker\fake;

test('Creates correct sql for date', function () {

    $prefix = config('stickle.database.tablePrefix');

    $date = fake()->date();

    $filter = Filter::date('a_column')
        ->willOccurBefore($date);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where ((data->>'a_column')::date < ? and (data->>'a_column')::date > ?)", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($date);

    expect(Carbon\Carbon::createFromDate(collect($builder->getBindings())->last())->toDateString())->toEqual(Carbon\Carbon::today()->toDateString());
});

test('Creates correct sql for datetime', function () {

    $prefix = config('stickle.database.tablePrefix');

    $datetime = fake()->datetime();

    $filter = Filter::datetime('a_column')
        ->willOccurBefore($datetime);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where ((data->>'a_column')::datetime < ? and (data->>'a_column')::datetime > ?)", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($datetime);

    // This is testing 'now' but we can only test on the date not the datetime
    expect(Carbon\Carbon::createFromDate(collect($builder->getBindings())->last())->toDateString())->toEqual(Carbon\Carbon::today()->toDateString());
});

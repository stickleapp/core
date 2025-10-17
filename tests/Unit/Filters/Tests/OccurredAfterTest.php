<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

use function Pest\Faker\fake;

test('Creates correct sql for date', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $date = fake()->date();

    $filter = Filter::date('a_column')
        ->occurredAfter($date);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->>'a_column'::date > ? and data->>'a_column'::date < ?)", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($date);

    expect(Date::createFromDate(collect($builder->getBindings())->last())->toDateString())->toEqual(Date::today()->toDateString());
});

test('Creates correct sql for datetime', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $datetime = fake()->datetime();

    $filter = Filter::datetime('a_column')
        ->occurredAfter($datetime);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->>'a_column'::datetime > ? and data->>'a_column'::datetime < ?)", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($datetime);

    // This is testing 'now' but we can only test on the date not the datetime
    expect(Date::createFromDate(collect($builder->getBindings())->last())->toDateString())->toEqual(Date::today()->toDateString());
});

test('works with relative dates', function (): void {

    $filter = Filter::date('a_column')
        ->occurredAfter(now()->subDays(10)->format('Y-m-d'));

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->>'a_column'::date > ? and data->>'a_column'::date < ?)", config('stickle.database.tablePrefix'))
    );

    expect($builder->getBindings()[0])->toEqual(now()->subDays(10)->format('Y-m-d'));
    expect(Date::createFromDate($builder->getBindings()[1])->toDateString())->toEqual(Date::today()->toDateString());
});

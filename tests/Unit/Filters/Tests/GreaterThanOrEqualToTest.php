<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

use function Pest\Faker\fake;

test('Creates correct sql for date', function () {

    $prefix = config('stickle.database.tablePrefix');

    $date = fake()->date();

    $filter = Filter::date('a_column')
        ->greaterThanOrEqualTo($date);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::date >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($date);
});

test('Creates correct sql for datetime', function () {

    $prefix = config('stickle.database.tablePrefix');

    $datetime = fake()->datetime();

    $filter = Filter::datetime('a_column')
        ->greaterThanOrEqualTo($datetime);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::datetime >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($datetime);
});

test('Creates correct sql for text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $uuid = fake()->uuid();

    $filter = Filter::text('a_column')
        ->greaterThanOrEqualTo($uuid);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::text >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($uuid);
});

test('Creates correct sql for number', function () {

    $prefix = config('stickle.database.tablePrefix');

    $number = fake()->randomNumber();

    $filter = Filter::number('a_column')
        ->greaterThanOrEqualTo($number);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->'a_column')::numeric >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($number);
});

test('works with relative dates', function () {

    $filter = Filter::date('a_column')
        ->greaterThanOrEqualTo(now()->subDays(10)->format('Y-m-d'));

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::date >= ?", config('stickle.database.tablePrefix'))
    );

    expect($builder->getBindings())->toEqual([
        now()->subDays(10)->format('Y-m-d'),
    ]);
});

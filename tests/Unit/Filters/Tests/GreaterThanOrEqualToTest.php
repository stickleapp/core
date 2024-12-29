<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Workbench\App\Models\User;

use function Pest\Faker\fake;

test('Creates correct sql for date', function () {

    $prefix = config('cascade.database.tablePrefix');

    $date = fake()->date();

    $filter = Filter::date('a_column')
        ->greaterThanOrEqualTo($date);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::date >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($date);
});

test('Creates correct sql for datetime', function () {

    $prefix = config('cascade.database.tablePrefix');

    $datetime = fake()->datetime();

    $filter = Filter::datetime('a_column')
        ->greaterThanOrEqualTo($datetime);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::datetime >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($datetime);
});

test('Creates correct sql for text', function () {

    $prefix = config('cascade.database.tablePrefix');

    $uuid = fake()->uuid();

    $filter = Filter::text('a_column')
        ->greaterThanOrEqualTo($uuid);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::text >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($uuid);
});

test('Creates correct sql for number', function () {

    $prefix = config('cascade.database.tablePrefix');

    $number = fake()->randomNumber();

    $filter = Filter::number('a_column')
        ->greaterThanOrEqualTo($number);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::numeric >= ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe($number);
});

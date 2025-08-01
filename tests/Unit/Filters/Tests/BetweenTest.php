<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql for text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->between('a', 'b');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::text between ? and ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe('a');
    expect(collect($builder->getBindings())->last())->toBe('b');

});

test('Creates correct sql for number', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column')
        ->between(1, 3);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->'a_column')::numeric between ? and ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe(1);
    expect(collect($builder->getBindings())->last())->toBe(3);
});

test('Creates correct sql for date', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::date('a_column')
        ->between('2024-11-01', '2024-12-01');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::date between ? and ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe('2024-11-01');
    expect(collect($builder->getBindings())->last())->toBe('2024-12-01');
});

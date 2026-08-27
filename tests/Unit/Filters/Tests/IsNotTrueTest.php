<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column')
        ->isNotTrue();

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where ((data->'a_column')::boolean != true or (data->'a_column')::boolean is null)", $prefix)
    );
});

test('Joins with or when the operator is or', function (): void {

    $filter = Filter::boolean('a_column')->isNotTrue();

    $builder = User::query()->where('id', '>', 0);

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'or');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where \"id\" > ? or ((data->'a_column')::boolean != true or (data->'a_column')::boolean is null)"
    );
});

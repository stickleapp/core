<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->equals('something');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->>'a_column')::text = ?", $prefix)
    );
});

test('works with relative dates', function () {

    $filter = Filter::date('a_column')
        ->equals(now()->subDays(10)->format('Y-m-d'));

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->>'a_column')::date = ?", config('stickle.database.tablePrefix'))
    );

    expect($builder->getBindings())->toEqual([
        now()->subDays(10)->format('Y-m-d'),
    ]);
});

<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->equals('something');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" where a_column::text = ?', $prefix)
    );
});

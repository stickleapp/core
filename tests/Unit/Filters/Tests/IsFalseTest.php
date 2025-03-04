<?php

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = ('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column')
        ->isFalse();

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::boolean = false", $prefix)
    );
});

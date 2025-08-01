<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column')
        ->isFalse();

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (data->'a_column')::boolean = false", $prefix)
    );
});

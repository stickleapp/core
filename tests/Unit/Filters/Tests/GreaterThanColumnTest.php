<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $filter = Filter::number('first_column')
        ->greaterThanColumn('second_column');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where (data->>'first_column')::numeric > \"second_column\""
    );
});

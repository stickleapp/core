<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $filter = Filter::text('first_column')
        ->equalsColumn('second_column');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        "select * from \"users\" where data->>'first_column'::text = \"second_column\""
    );
});

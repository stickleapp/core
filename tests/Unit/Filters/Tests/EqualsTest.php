<?php

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = Config::string('stickle.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->equals('something');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::text = ?", $prefix)
    );
});

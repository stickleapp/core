<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql for text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->contains('donkey');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where (model_attributes->>'a_column')::text ilike ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe('%donkey%');
});

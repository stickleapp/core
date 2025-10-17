<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql for text', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column')
        ->contains('donkey');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->>'a_column'::text ilike ?", $prefix)
    );

    expect(collect($builder->getBindings())->first())->toBe('%donkey%');
});

test('Creates correct sql for number', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $number = '0123456789';

    $filter = Filter::number('a_column')
        ->contains('234');

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $filter->test->applyFilter($builder, $target, 'and');

    expect($builder->toSql())->toBe(
        sprintf("select * from \"users\" where data->'a_column'::text ilike ?", $prefix)
    );
    // dd($builder->getBindings());

    // expect(collect($builder->getBindings())->first())->toBe($number);
});

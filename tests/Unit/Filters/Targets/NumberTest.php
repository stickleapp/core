<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Number;
use Workbench\App\Models\User;

test('text() sets target as text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Number::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe("(data->'a_column')::numeric");

});

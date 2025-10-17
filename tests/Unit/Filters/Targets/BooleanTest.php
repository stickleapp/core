<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Boolean;
use Workbench\App\Models\User;

test('boolean() sets target as Boolean', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Boolean::class);

});

test('Casts target property as boolean', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe("(data->'a_column')::boolean");

});

<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Boolean;

test('boolean() sets target as Boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target)->toBeInstanceOf(Boolean::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target->castProperty())->toBe("(data->'a_column')::boolean");

});

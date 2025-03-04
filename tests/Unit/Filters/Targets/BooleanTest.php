<?php

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Boolean;

test('boolean() sets target as Boolean', function () {

    $prefix = ('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target)->toBeInstanceOf(Boolean::class);

});

test('Casts target property as boolean', function () {

    $prefix = ('stickle.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target->castProperty())->toBe("(model_attributes->>'a_column')::boolean");

});

<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Number;

test('text() sets target as text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target)->toBeInstanceOf(Number::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target->castProperty())->toBe("(model_attributes->>'a_column')::numeric");

});

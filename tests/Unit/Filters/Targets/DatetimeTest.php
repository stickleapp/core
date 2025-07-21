<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Datetime;

test('boolean() sets target as Datetime', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target)->toBeInstanceOf(Datetime::class);

});

test('Casts target property as datetime', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target->castProperty())->toBe("(data->>'a_column')::datetime");
});

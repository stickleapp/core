<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Text;

test('text() sets target as text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column');

    expect($filter->target)->toBeInstanceOf(Text::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column');

    expect($filter->target->castProperty())->toBe("(data->>'a_column')::text");
});

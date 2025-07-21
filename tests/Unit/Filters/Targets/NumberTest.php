<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Number;
use StickleApp\Core\Filters\Targets\NumberDelta;

test('text() sets target as text', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target)->toBeInstanceOf(Number::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target->castProperty())->toBe("(data->>'a_column')::numeric");

});

test('`decreased` changes target class', function () {

    $filter = Filter::number('score');

    expect($filter->target)->toBeInstanceOf(Number::class);

    $filter->decreased(
        [now()->subYears(1), now()],
    );

    expect($filter->target)->toBeInstanceOf(NumberDelta::class);

});

test('`increased` changes target class', function () {

    $filter = Filter::number('score');

    expect($filter->target)->toBeInstanceOf(Number::class);

    $filter->increased([now()->subYears(1), now()]);

    expect($filter->target)->toBeInstanceOf(NumberDelta::class);

});

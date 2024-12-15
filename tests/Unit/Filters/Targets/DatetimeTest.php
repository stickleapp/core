<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\Datetime;

test('boolean() sets target as Datetime', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target)->toBeInstanceOf(Datetime::class);

});

test('Casts target property as datetime', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target->castProperty())->toBe('a_column::datetime');

});

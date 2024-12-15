<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\Text;

test('text() sets target as text', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::text('a_column');

    expect($filter->target)->toBeInstanceOf(Text::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::text('a_column');

    expect($filter->target->castProperty())->toBe('a_column::text');

});

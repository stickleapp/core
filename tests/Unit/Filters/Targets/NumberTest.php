<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\Number;

test('text() sets target as text', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target)->toBeInstanceOf(Number::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::number('a_column');

    expect($filter->target->castProperty())->toBe('model_attributes->a_column::numeric');

});

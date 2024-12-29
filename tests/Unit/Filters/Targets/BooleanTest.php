<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\Boolean;

test('boolean() sets target as Boolean', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target)->toBeInstanceOf(Boolean::class);

});

test('Casts target property as boolean', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::boolean('a_column');

    expect($filter->target->castProperty())->toBe("(model_attributes->>'a_column')::boolean");

});

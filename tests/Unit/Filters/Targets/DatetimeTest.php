<?php

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Datetime;

test('boolean() sets target as Datetime', function () {

    $prefix = Config::string('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target)->toBeInstanceOf(Datetime::class);

});

test('Casts target property as datetime', function () {

    $prefix = Config::string('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    expect($filter->target->castProperty())->toBe("(model_attributes->>'a_column')::datetime");

});

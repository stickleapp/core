<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Date;

test('date() sets target as Date', function () {

    $filter = Filter::date('a_column');

    expect($filter->target)->toBeInstanceOf(Date::class);

});

test('casts target property as date', function () {

    $filter = Filter::date('a_column');

    expect($filter->target->castProperty())->toBe("data->>'a_column'::date");

});

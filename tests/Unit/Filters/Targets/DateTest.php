<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Date;
use Workbench\App\Models\User;

test('date() sets target as Date', function () {

    $filter = Filter::date('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Date::class);

});

test('casts target property as date', function () {

    $filter = Filter::date('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe("data->>'a_column'::date");

});

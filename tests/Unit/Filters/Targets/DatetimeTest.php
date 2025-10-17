<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Datetime;
use Workbench\App\Models\User;

test('datetime() sets target as Datetime', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Datetime::class);

});

test('Casts target property as datetime', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::datetime('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe("data->>'a_column'::datetime");
});

<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Text;
use Workbench\App\Models\User;

test('text() sets target as text', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Text::class);

});

test('Casts target property as boolean', function (): void {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::text('a_column');

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe("data->>'a_column'::text");
});

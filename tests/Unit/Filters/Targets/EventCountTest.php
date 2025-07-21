<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\EventCount;
use StickleApp\Core\Filters\Targets\EventCountDelta;
use Workbench\App\Models\User;

test('`decreased` chanages target class', function () {

    $filter = Filter::eventCount('clicked:something');

    expect($filter->target)->toBeInstanceOf(EventCount::class);

    $filter->decreased(
        [now()->subYears(2), now()->subYears(1)],
        [now()->subYears(1), now()],
    );

    expect($filter->target)->toBeInstanceOf(EventCountDelta::class);

});

test('`increased` chanages target class', function () {

    $filter = Filter::eventCount('clicked:something');

    expect($filter->target)->toBeInstanceOf(EventCount::class);

    $filter->increased(
        [now()->subYears(2), now()->subYears(1)],
        [now()->subYears(1), now()],
    );
    expect($filter->target)->toBeInstanceOf(EventCountDelta::class);

});

test('Creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::eventCount('clicked:something')
        ->greaterThan(10)
        ->between(now()->subYears(1), now());

    $builder = User::query();

    $filter->target->applyJoin($builder);

    $joinKey = $filter->target->joinKey();

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" left join (select "model_class", "object_uid", sum(event_count) as event_count from "%sevents_rollup_1day" where "event_name" = ? and "day"::date >= ? and "day"::date < ? group by "model_class", "object_uid") as "%s" on "%s"."object_uid" = "users"."object_uid" and "%s"."model_class" = "users"."model_class"', $prefix, $joinKey, $joinKey, $joinKey)
    );
});

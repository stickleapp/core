<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\EventCount;
use Dclaysmith\LaravelCascade\Filters\Targets\EventCountDelta;
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

test('Delta creates correct sql', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::eventCount('clicked:something')->increased(
        [now()->subYears(2), now()->subYears(1)],
        [now()->subYears(1), now()],
    )
        ->greaterThan(10)
        ->between(now()->subYears(1), now());

    $builder = User::query();

    $filter->target->applyJoin($builder);

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" inner join (select "model", "object_uid",  SUM(event_count) OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL \'59 day\' PRECEDING AND INTERVAL \'30 day\' PRECEDING) - SUM(event_count) OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL \'29 day\' PRECEDING AND CURRENT ROW) - AS delta  from "%sevents_rollup_1day" where "event_name" = ?) as "3c3311a35fdc500fd5fa76ceda061d2d" on "3c3311a35fdc500fd5fa76ceda061d2d"."object_uid" = "users"."object_uid" and "3c3311a35fdc500fd5fa76ceda061d2d"."model" = "users"."model"', $prefix)
    );
});

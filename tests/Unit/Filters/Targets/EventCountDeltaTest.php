<?php

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

test('Delta creates correct sql', function () {

    $prefix = Config::string('stickle.database.tablePrefix');

    $filter = Filter::eventCount('clicked:something')->increased(
        [now()->subYears(2), now()->subYears(1)],
        [now()->subYears(1), now()],
    )
        ->greaterThan(10)
        ->between(now()->subYears(1), now());

    $builder = User::query();

    $filter->target->applyJoin($builder);

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" left join (select "model", "object_uid",  SUM(event_count) OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL \'59 day\' PRECEDING AND INTERVAL \'30 day\' PRECEDING) - SUM(event_count) OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL \'29 day\' PRECEDING AND CURRENT ROW) - AS delta  from "%sevents_rollup_1day" where "event_name" = ?) as "24b0ef4445b20b487319ce60bf964967" on "24b0ef4445b20b487319ce60bf964967"."object_uid" = "users"."object_uid" and "24b0ef4445b20b487319ce60bf964967"."model" = "users"."model"', $prefix)
    );
});

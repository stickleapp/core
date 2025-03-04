<?php

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::eventCount('clicked:something')
        ->greaterThan(10)
        ->between(now()->subYears(1), now());

    $builder = User::query();

    $filter->target->applyJoin($builder);

    $joinKey = $filter->target->joinKey();

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" left join (select "model", "object_uid", sum(event_count) as event_count from "%sevents_rollup_1day" where "event_name" = ? and "day"::date >= ? and "day"::date < ? group by "model", "object_uid") as "%s" on "%s"."object_uid" = "users"."object_uid" and "%s"."model" = "users"."model"', $prefix, $joinKey, $joinKey, $joinKey)
    );
});

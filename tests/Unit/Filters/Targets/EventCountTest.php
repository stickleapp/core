<?php

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Creates correct sql', function () {

    $prefix = config('cascade.database.tablePrefix');

    $filter = Filter::eventCount('clicked:something')
        ->greaterThan(10)
        ->between(now()->subYears(1), now());

    $builder = User::query();

    $filter->target->applyJoin($builder);

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" inner join (select "model", "object_uid", sum(event_count) as event_count from "%sevents_rollup_1day" where "event_name" = ? and "day"::date >= ? and "day"::date < ? group by "model", "object_uid") as "3c3311a35fdc500fd5fa76ceda061d2d" on "3c3311a35fdc500fd5fa76ceda061d2d"."object_uid" = "users"."object_uid" and "3c3311a35fdc500fd5fa76ceda061d2d"."model" = "users"."model"', $prefix)
    );
});

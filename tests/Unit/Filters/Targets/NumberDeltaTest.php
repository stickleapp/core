<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

test('Delta creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('score')->increased(
        [now()->subYears(1), now()],
    )->greaterThan(10);

    $builder = User::query();

    $filter->target->applyJoin($builder);

    $joinKey = $filter->target->joinKey();

    expect($builder->toSql())->toBe(
        sprintf('select * from "users" left join (select "model_class", "object_uid",  LAST_VALUE((data->>\'score\')::numeric) OVER (PARTITION BY model_class, object_uid ORDER BY day ASC ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) - FIRST_VALUE((data->>\'score\')::numeric) OVER (PARTITION BY model_class, object_uid ORDER BY day ASC) AS delta  from "stc_model_attribute_audit" where "attribute" = ? and "day" between ? and ?) as "56aaa2338e4c1c96b7562ceff2496bd2" on "56aaa2338e4c1c96b7562ceff2496bd2"."object_uid" = "users"."object_uid" and "56aaa2338e4c1c96b7562ceff2496bd2"."model_class" = "users"."model_class"', $prefix, $joinKey, $joinKey, $joinKey)
    );
});

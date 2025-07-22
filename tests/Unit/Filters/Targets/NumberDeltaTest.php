<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\NumberDelta;
use Workbench\App\Models\User;

test('numberDelta() sets target as NumberDelta', function () {

    $filter = Filter::numberDelta('score', [now()->subDays(30), now()]);

    expect($filter->target)->toBeInstanceOf(NumberDelta::class);

});

test('Delta creates correct sql', function () {

    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::number('score')->increased(
        [now()->subYears(1), now()],
    )->greaterThan(10);

    $builder = User::query();

    $filter->target->applyJoin($builder);

    $joinKey = $filter->target->joinKey();

    // Normalize SQL by replacing multiple spaces and newlines with a single space
    expect(preg_replace('/\s+/', ' ', $builder->toSql()))->toBe(
        preg_replace('/\s+/', ' ', sprintf('select * from "users" left join (select "model_class", "object_uid", LAST_VALUE((data->>\'score\')::numeric) OVER (PARTITION BY model_class, object_uid ORDER BY day ASC ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) - FIRST_VALUE((data->>\'score\')::numeric) OVER (PARTITION BY model_class, object_uid ORDER BY day ASC) AS delta from "stc_model_attribute_audit" where "attribute" = ? and "day" between ? and ?) as "%s" on "%s"."object_uid" = "users"."object_uid" and "%s"."model_class" = "users"."model_class"', $joinKey, $joinKey, $joinKey))
    );
});

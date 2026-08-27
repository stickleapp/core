<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

/**
 * The set had Equals, EqualsColumn and NotEqualsColumn but no negation against
 * a literal, so a rule as ordinary as "the plan is not none" could not be
 * expressed without inventing a 0/1 attribute to stand in for it.
 */
test('Creates correct sql', function (): void {
    $filter = Filter::text('plan')->notEquals('none');

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->getTarget($builder), 'and');

    expect($builder->toSql())->toBe(
        'select * from "users" where data->>\'plan\'::text != ?'
    );

    expect($builder->getBindings())->toEqual(['none']);
});

test('ors when the filter is applied with the or operator', function (): void {
    $filter = Filter::text('plan')->notEquals('none');

    $builder = User::query()->where('name', 'Someone');

    $filter->test->applyFilter($builder, $filter->getTarget($builder), 'or');

    expect($builder->toSql())->toContain('or data->>\'plan\'::text != ?');
});

test('negates a number without stringifying it', function (): void {
    $filter = Filter::number('seat_count')->notEquals(0);

    $builder = User::query();

    $filter->test->applyFilter($builder, $filter->getTarget($builder), 'and');

    expect($builder->toSql())->toBe(
        'select * from "users" where (data->\'seat_count\')::numeric != ?'
    );

    expect($builder->getBindings())->toEqual([0]);
});

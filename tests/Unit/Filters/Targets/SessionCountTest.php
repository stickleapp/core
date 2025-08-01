<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\SessionCountAggregate;
use Workbench\App\Models\User;

test('sessionCount() with aggregate creates SessionCountAggregate target', function () {
    $filter = Filter::sessionCount()
        ->sum()
        ->betweenDates(now()->subDays(7), now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(SessionCountAggregate::class);
    expect($target->aggregate)->toBe('sum');
});

test('SessionCountAggregate creates correct SQL', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::sessionCount()
        ->count()
        ->betweenDates(now()->subDays(30), now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('count(session_count)');
    expect($sql)->toContain($prefix.'sessions_rollup_1day');
});

test('SessionCount requires aggregate method', function () {
    expect(function () {
        $filter = Filter::sessionCount()
            ->betweenDates(now()->subDays(7), now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Aggregate is required');
});

test('SessionCount requires date range', function () {
    expect(function () {
        $filter = Filter::sessionCount()
            ->sum();

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Current DateRange is required');
});

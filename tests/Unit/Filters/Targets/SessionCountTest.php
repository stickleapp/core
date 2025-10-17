<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\SessionCountAggregate;
use Workbench\App\Models\User;

test('sessionCount() with aggregate creates SessionCountAggregate target', function (): void {
    $filter = Filter::sessionCount()
        ->sum()
        ->betweenDates(startDate: now()->subDays(7), endDate: now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(SessionCountAggregate::class);
    expect($target->aggregate)->toBe('sum');
});

test('SessionCountAggregate creates correct SQL', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::sessionCount()
        ->count()
        ->betweenDates(startDate: now()->subDays(30), endDate: now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('count(session_count)');
    expect($sql)->toContain($prefix.'sessions_rollup_1day');
});

test('SessionCount requires aggregate method', function (): void {
    expect(function (): void {
        $filter = Filter::sessionCount()
            ->betweenDates(startDate: now()->subDays(7), endDate: now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(InvalidArgumentException::class, 'Aggregate is required');
});

test('SessionCount requires date range', function (): void {
    expect(function (): void {
        $filter = Filter::sessionCount()
            ->sum();

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(InvalidArgumentException::class, 'Current DateRange is required');
});

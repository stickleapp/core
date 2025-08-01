<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\EventCountAggregate;
use Workbench\App\Models\User;

test('eventCount() with aggregate creates EventCountAggregate target', function () {
    $filter = Filter::eventCount(event: 'clicked:button')
        ->sum()
        ->betweenDates(now()->subDays(7), now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(EventCountAggregate::class);
    expect($target->event)->toBe('clicked:button');
    expect($target->aggregate)->toBe('sum');
});

test('EventCountAggregate creates correct SQL', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::eventCount(event: 'user:login')
        ->count()
        ->betweenDates(now()->subDays(30), now());

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('count(event_count)');
    expect($sql)->toContain($prefix.'events_rollup_1day');
});

test('EventCount requires event argument', function () {
    expect(function () {
        $filter = Filter::eventCount(event: '')
            ->sum()
            ->betweenDates(now()->subDays(7), now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Event is required');
});

test('EventCount requires aggregate method', function () {
    expect(function () {
        $filter = Filter::eventCount(event: 'clicked:button')
            ->betweenDates(now()->subDays(7), now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Aggregate is required');
});

test('EventCount requires date range', function () {
    expect(function () {
        $filter = Filter::eventCount(event: 'clicked:button')
            ->sum();

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Current DateRange is required');
});

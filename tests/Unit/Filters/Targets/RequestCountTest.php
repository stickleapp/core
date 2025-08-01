<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\RequestCountAggregate;
use Workbench\App\Models\User;

test('requestCount() with aggregate creates RequestCountAggregate target', function () {
    $filter = Filter::requestCount(url: '/api/something')
        ->sum()
        ->betweenDates(now()->subDays(7), now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(RequestCountAggregate::class);
    expect($target->url)->toBe('/api/something');
    expect($target->aggregate)->toBe('sum');
});

test('RequestCountAggregate creates correct SQL', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::requestCount(url: '/api/something')
        ->sum()
        ->betweenDates(now()->subDays(7), now());

    $builder = User::query();
    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('sum(request_count)');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
});

test('RequestCount requires URL argument', function () {
    expect(function () {
        $filter = Filter::requestCount(url: '')
            ->sum()
            ->betweenDates(now()->subDays(7), now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'URL is required');
});

test('RequestCount requires aggregate method', function () {
    expect(function () {
        $filter = Filter::requestCount(url: '/api/something')
            ->betweenDates(now()->subDays(7), now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Aggregate is required');
});

test('RequestCount requires date range', function () {
    expect(function () {
        $filter = Filter::requestCount(url: '/api/something')
            ->sum();

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Current DateRange is required');
});

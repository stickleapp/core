<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\EventCountAggregateDelta;
use Workbench\App\Models\User;

test('EventCount with delta creates EventCountAggregateDelta target', function () {
    $currentPeriod = [now()->subDays(7), now()];
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];

    $filter = Filter::eventCount(event: 'user:login')
        ->sum()
        ->increased()
        ->betweenDateRanges($previousPeriod, $currentPeriod);

    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(EventCountAggregateDelta::class);
    expect($target->event)->toBe('user:login');
    expect($target->aggregate)->toBe('sum');
    expect($target->deltaVerb)->toBe('increased');
    // expect($target->currentPeriod)->toBe($currentPeriod);
    // expect($target->previousPeriod)->toBe($previousPeriod);
});

test('EventCountAggregateDelta creates correct SQL', function () {
    $prefix = config('stickle.database.tablePrefix');
    $currentPeriod = [now()->subDays(7), now()];
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];

    $filter = Filter::eventCount(event: 'page:view')
        ->count()
        ->decreased()
        ->betweenDateRanges($previousPeriod, $currentPeriod);

    $builder = User::query();

    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
});

test('EventCount delta requires both date ranges', function () {
    expect(function () {
        $filter = Filter::eventCount(event: 'clicked:button')
            ->sum()
            ->increased()
            ->betweenDates(startDate: now()->subDays(7), endDate: now());

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'Delta type');
});

test('EventCount with compare date range requires delta type', function () {
    expect(function () {
        $currentPeriod = [now()->subDays(7), now()];
        $previousPeriod = [now()->subDays(14), now()->subDays(7)];

        $filter = Filter::eventCount(event: 'form:submit')
            ->sum()
            ->betweenDateRanges($previousPeriod, $currentPeriod);

        $builder = User::query();
        $filter->getTarget($builder);
    })->toThrow(\InvalidArgumentException::class, 'no delta type');
});

test('EventCount supports different delta types', function () {
    $currentPeriod = [now()->subDays(7), now()];
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];

    $increasedFilter = Filter::eventCount(event: 'video:play')
        ->sum()
        ->increased()
        ->betweenDateRanges($previousPeriod, $currentPeriod);

    $decreasedFilter = Filter::eventCount(event: 'video:play')
        ->sum()
        ->decreased()
        ->betweenDateRanges($previousPeriod, $currentPeriod);

    $changedFilter = Filter::eventCount(event: 'video:play')
        ->sum()
        ->changed()
        ->betweenDateRanges($previousPeriod, $currentPeriod);

    $builder = User::query();

    expect($increasedFilter->getTarget($builder)->deltaVerb)->toBe('increased');
    expect($decreasedFilter->getTarget($builder)->deltaVerb)->toBe('decreased');
    expect($changedFilter->getTarget($builder)->deltaVerb)->toBe('changed');
});

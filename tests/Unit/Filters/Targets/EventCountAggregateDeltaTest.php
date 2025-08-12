<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\EventCountAggregateDelta;
use Workbench\App\Models\User;

test('EventCountAggregateDelta has correct base target', function () {
    expect(EventCountAggregateDelta::baseTarget())->toBe('StickleApp\\Core\\Filters\\Targets\\EventCount');
});

test('EventCountAggregateDelta sets correct properties', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $event = 'user:login';
    $aggregate = 'count';
    $deltaVerb = 'increased';
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new EventCountAggregateDelta($prefix, $builder, $event, $aggregate, $deltaVerb, $previousPeriod, $currentPeriod);

    expect($target->event)->toBe($event);
    expect($target->aggregate)->toBe($aggregate);
    expect($target->deltaVerb)->toBe($deltaVerb);
    expect($target->previousPeriod)->toBe($previousPeriod);
    expect($target->currentPeriod)->toBe($currentPeriod);
    expect($target->property())->toBe($event);
});

test('EventCountAggregateDelta creates correct cast property', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new EventCountAggregateDelta($prefix, $builder, 'user:logout', 'sum', 'decreased', $previousPeriod, $currentPeriod);

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('event_count_aggregate_delta_');
    expect($castProperty)->toContain($target->joinKey());
});

test('EventCountAggregateDelta generates consistent join key', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new EventCountAggregateDelta($prefix, $builder, 'clicked:cta', 'avg', 'changed', $previousPeriod, $currentPeriod);

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('EventCountAggregateDelta applies join correctly', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new EventCountAggregateDelta($prefix, $builder, 'form:submit', 'min', 'increased', $previousPeriod, $currentPeriod);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
    expect($sql)->toContain('"name" = ?');
    expect($sql)->toContain('CASE WHEN');
});

<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\SessionCountAggregateDelta;
use Workbench\App\Models\User;

test('SessionCountAggregateDelta has correct base target', function () {
    expect(SessionCountAggregateDelta::baseTarget())->toBe('StickleApp\\Core\\Filters\\Targets\\SessionCount');
});

test('SessionCountAggregateDelta sets correct properties', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $aggregate = 'count';
    $deltaVerb = 'increased';
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new SessionCountAggregateDelta($prefix, $builder, $aggregate, $deltaVerb, $previousPeriod, $currentPeriod);

    expect($target->aggregate)->toBe($aggregate);
    expect($target->deltaVerb)->toBe($deltaVerb);
    expect($target->previousPeriod)->toBe($previousPeriod);
    expect($target->currentPeriod)->toBe($currentPeriod);
    expect($target->property())->toBe('session_count');
});

test('SessionCountAggregateDelta creates correct cast property', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new SessionCountAggregateDelta($prefix, $builder, 'sum', 'decreased', $previousPeriod, $currentPeriod);

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('session_count_aggregate_delta_');
    expect($castProperty)->toContain($target->joinKey());
});

test('SessionCountAggregateDelta generates consistent join key', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new SessionCountAggregateDelta($prefix, $builder, 'avg', 'changed', $previousPeriod, $currentPeriod);

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('SessionCountAggregateDelta applies join correctly', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new SessionCountAggregateDelta($prefix, $builder, 'min', 'increased', $previousPeriod, $currentPeriod);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'sessions_rollup_1day');
    expect($sql)->toContain('CASE WHEN');
});

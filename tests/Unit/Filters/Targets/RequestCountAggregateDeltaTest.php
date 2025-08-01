<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\RequestCountAggregateDelta;
use Workbench\App\Models\User;

test('RequestCountAggregateDelta has correct base target', function () {
    expect(RequestCountAggregateDelta::baseTarget())->toBe('StickleApp\\Core\\Filters\\Targets\\RequestCount');
});

test('RequestCountAggregateDelta sets correct properties', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $url = '/api/users';
    $aggregate = 'sum';
    $deltaVerb = 'increased';
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new RequestCountAggregateDelta($prefix, $builder, $url, $aggregate, $deltaVerb, $previousPeriod, $currentPeriod);

    expect($target->url)->toBe($url);
    expect($target->aggregate)->toBe($aggregate);
    expect($target->deltaVerb)->toBe($deltaVerb);
    expect($target->previousPeriod)->toBe($previousPeriod);
    expect($target->currentPeriod)->toBe($currentPeriod);
    expect($target->property())->toBe($url);
});

test('RequestCountAggregateDelta creates correct cast property', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new RequestCountAggregateDelta($prefix, $builder, '/api/users', 'sum', 'increased', $previousPeriod, $currentPeriod);

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('request_count_aggregate_delta_');
    expect($castProperty)->toContain($target->joinKey());
});

test('RequestCountAggregateDelta generates consistent join key', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new RequestCountAggregateDelta($prefix, $builder, '/api/users', 'count', 'changed', $previousPeriod, $currentPeriod);

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('RequestCountAggregateDelta applies join correctly', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $previousPeriod = [now()->subDays(14), now()->subDays(7)];
    $currentPeriod = [now()->subDays(7), now()];

    $target = new RequestCountAggregateDelta($prefix, $builder, '/api/users', 'avg', 'decreased', $previousPeriod, $currentPeriod);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
    expect($sql)->toContain('"url" = ?');
    expect($sql)->toContain('CASE WHEN');
});

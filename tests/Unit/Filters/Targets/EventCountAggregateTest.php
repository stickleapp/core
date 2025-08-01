<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\EventCountAggregate;
use Workbench\App\Models\User;

test('EventCountAggregate has correct base target', function () {
    expect(EventCountAggregate::baseTarget())->toBe('StickleApp\\Core\\Filters\\Targets\\EventCount');
});

test('EventCountAggregate sets correct properties', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $event = 'user:login';
    $aggregate = 'sum';
    $startDate = now()->subDays(7);
    $endDate = now();

    $target = new EventCountAggregate($prefix, $builder, $event, $aggregate, $startDate, $endDate);

    expect($target->event)->toBe($event);
    expect($target->aggregate)->toBe($aggregate);
    expect($target->startDate)->toBe($startDate);
    expect($target->endDate)->toBe($endDate);
    expect($target->property())->toBe($event);
});

test('EventCountAggregate creates correct cast property', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new EventCountAggregate($prefix, $builder, 'user:login', 'avg', now()->subDays(7), now());

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('event_count_aggregate_');
    expect($castProperty)->toContain($target->joinKey());
});

test('EventCountAggregate generates consistent join key', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new EventCountAggregate($prefix, $builder, 'clicked:button', 'count', now()->subDays(7), now());

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('EventCountAggregate applies join correctly', function () {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new EventCountAggregate($prefix, $builder, 'page:view', 'max', now()->subDays(7), now());

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('max(event_count)');
    expect($sql)->toContain($prefix.'events_rollup_1day');
    expect($sql)->toContain('"event_name" = ?');
});

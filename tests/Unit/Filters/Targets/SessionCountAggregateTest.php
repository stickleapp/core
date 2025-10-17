<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\SessionCount;
use StickleApp\Core\Filters\Targets\SessionCountAggregate;
use Workbench\App\Models\User;

test('SessionCountAggregate has correct base target', function (): void {
    expect(SessionCountAggregate::baseTarget())->toBe(SessionCount::class);
});

test('SessionCountAggregate sets correct properties', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $aggregate = 'sum';
    $startDate = now()->subDays(7);
    $endDate = now();

    $target = new SessionCountAggregate($prefix, $builder, $aggregate, $startDate, $endDate);

    expect($target->aggregate)->toBe($aggregate);
    expect($target->startDate)->toBe($startDate);
    expect($target->endDate)->toBe($endDate);
    expect($target->property())->toBe('session_count');
});

test('SessionCountAggregate creates correct cast property', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new SessionCountAggregate($prefix, $builder, 'avg', now()->subDays(7), now());

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('session_count_aggregate_');
    expect($castProperty)->toContain($target->joinKey());
});

test('SessionCountAggregate generates consistent join key', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new SessionCountAggregate($prefix, $builder, 'count', now()->subDays(7), now());

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('SessionCountAggregate applies join correctly', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new SessionCountAggregate($prefix, $builder, 'max', now()->subDays(7), now());

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('max(session_count)');
    expect($sql)->toContain($prefix.'sessions_rollup_1day');
});

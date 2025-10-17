<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\RequestCount;
use StickleApp\Core\Filters\Targets\RequestCountAggregate;
use Workbench\App\Models\User;

test('RequestCountAggregate has correct base target', function (): void {
    expect(RequestCountAggregate::baseTarget())->toBe(RequestCount::class);
});

test('RequestCountAggregate sets correct properties', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $url = '/api/users';
    $aggregate = 'sum';
    $startDate = now()->subDays(7);
    $endDate = now();

    $target = new RequestCountAggregate($prefix, $builder, $url, $aggregate, $startDate, $endDate);

    expect($target->url)->toBe($url);
    expect($target->aggregate)->toBe($aggregate);
    expect($target->startDate)->toBe($startDate);
    expect($target->endDate)->toBe($endDate);
    expect($target->property())->toBe($url);
});

test('RequestCountAggregate creates correct cast property', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new RequestCountAggregate($prefix, $builder, '/api/users', 'sum', now()->subDays(7), now());

    $castProperty = $target->castProperty();

    expect($castProperty)->toStartWith('request_sum_');
    expect($castProperty)->toContain($target->joinKey());
});

test('RequestCountAggregate generates consistent join key', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new RequestCountAggregate($prefix, $builder, '/api/users', 'count', now()->subDays(7), now());

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('RequestCountAggregate applies join correctly', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new RequestCountAggregate($prefix, $builder, '/api/users', 'avg', now()->subDays(7), now());

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('avg(request_count)');
    expect($sql)->toContain($prefix.'requests_rollup_1day');
    expect($sql)->toContain('"url" = ?');
});

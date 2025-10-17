<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Targets\Number;
use StickleApp\Core\Filters\Targets\NumberDelta;
use Workbench\App\Models\User;

test('NumberDelta has correct base target', function (): void {
    expect(NumberDelta::baseTarget())->toBe(Number::class);
});

test('NumberDelta sets correct properties', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $attribute = 'score';
    $startDate = now()->subDays(30);
    $endDate = now();

    $target = new NumberDelta($prefix, $builder, $attribute, $startDate, $endDate);

    expect($target->attribute)->toBe($attribute);
    expect($target->startDate)->toBe($startDate);
    expect($target->endDate)->toBe($endDate);
    expect($target->property())->toBe("data->>'{$attribute}'");
});

test('NumberDelta creates correct cast property', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new NumberDelta($prefix, $builder, 'score', now()->subDays(7), now());

    $castProperty = $target->castProperty();

    expect($castProperty)->toBe("data->>'score'::numeric");
});

test('NumberDelta generates consistent join key', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new NumberDelta($prefix, $builder, 'score', now()->subDays(7), now());

    $joinKey1 = $target->joinKey();
    $joinKey2 = $target->joinKey();

    expect($joinKey1)->toBe($joinKey2);
    expect($joinKey1)->toHaveLength(32); // MD5 hash length
});

test('NumberDelta creates correct SQL with period', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $startDate = now()->subDays(7);
    $endDate = now();
    $target = new NumberDelta($prefix, $builder, 'points', $startDate, $endDate);

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'model_attribute_audit');
    expect($sql)->toContain('LAST_VALUE');
    expect($sql)->toContain('FIRST_VALUE');
});

test('NumberDelta creates correct SQL without end date', function (): void {
    $prefix = config('stickle.database.tablePrefix');
    $builder = User::query();
    $target = new NumberDelta($prefix, $builder, 'balance', now()->subDays(7));

    $target->applyJoin();

    $sql = $builder->toSql();

    expect($sql)->toContain('left join');
    expect($sql)->toContain('delta');
    expect($sql)->toContain($prefix.'model_attribute_audit');
    expect($sql)->not->toContain('whereBetween');
});

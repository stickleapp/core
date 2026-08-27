<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Tests\Between;
use StickleApp\Core\Filters\Tests\Equals;
use StickleApp\Core\Filters\Tests\GreaterThan;
use StickleApp\Core\Filters\Tests\IsAfter;
use StickleApp\Core\Filters\Tests\IsBefore;
use StickleApp\Core\Filters\Tests\OccurredAfter;
use StickleApp\Core\Filters\Tests\OccurredBefore;
use StickleApp\Core\Filters\Tests\WillOccurAfter;
use StickleApp\Core\Filters\Tests\WillOccurBefore;
use StickleApp\Core\Tests\Fixtures\Filters\CastingTarget;
use Workbench\App\Models\User;

/**
 * castValue() is the target's hook for turning a caller's comparator into the
 * representation the column needs. A test class that binds the comparator
 * directly bypasses that hook, so the moment a target overrides castValue()
 * the class compares against an unconverted value. The base implementation
 * returns the value untouched, which is why this went unnoticed.
 */
function castingFilter(): array
{
    $builder = User::query();

    return [$builder, new CastingTarget($builder)];
}

test('GreaterThan casts its comparator', function (): void {
    [$builder, $target] = castingFilter();

    (new GreaterThan('10'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toBe(['cast:10']);
});

test('Equals casts its comparator', function (): void {
    [$builder, $target] = castingFilter();

    (new Equals('owner'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toBe(['cast:owner']);
});

test('IsAfter casts its comparator', function (): void {
    [$builder, $target] = castingFilter();

    (new IsAfter('2026-01-01'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toBe(['cast:2026-01-01']);
});

test('IsBefore casts its comparator', function (): void {
    [$builder, $target] = castingFilter();

    (new IsBefore('2026-01-01'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toBe(['cast:2026-01-01']);
});

test('Between casts both bounds', function (): void {
    [$builder, $target] = castingFilter();

    (new Between('1', '9'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toBe(['cast:1', 'cast:9']);
});

/**
 * The date group compares the comparator AND a "now" bound against the same
 * casted column, so both sides have to go through the same conversion.
 */
test('the date group casts the now bound as well as the comparator', function (string $class): void {
    [$builder, $target] = castingFilter();

    (new $class('2026-01-01'))->applyFilter($builder, $target, 'and');

    expect($builder->getBindings())->toHaveCount(2)
        ->and($builder->getBindings()[0])->toBe('cast:2026-01-01')
        ->and($builder->getBindings()[1])->toStartWith('cast:');
})->with([
    OccurredAfter::class,
    OccurredBefore::class,
    WillOccurAfter::class,
    WillOccurBefore::class,
]);

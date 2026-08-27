<?php

declare(strict_types=1);

use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
use Workbench\App\Models\User;

/**
 * Every lookup in getStickleChartData() falls back with ??, so a broken read
 * renders a plausible chart instead of failing. These assertions deliberately
 * avoid `label` alone as the only signal: 'User Rating' is also what the
 * default titleiser produces for 'user_rating', so a label-only test passes
 * whether or not the metadata was ever read.
 */
function chartDataFor(string $key): array
{
    return collect(User::getStickleChartData())->keyBy('key')->get($key);
}

test('chart data carries the description from the annotation', function (): void {
    expect(chartDataFor('user_rating')['description'])
        ->toBe('The 1 to 5 star rating of the user.');
});

test('chart data carries the data type from the annotation', function (): void {
    expect(chartDataFor('user_rating')['dataType'])->toBe(DataType::INTEGER);
});

/**
 * D3: the annotation and the views both say `primaryAggregate`; the reader
 * emitted `primaryAggregateType`, which nothing consumes.
 */
test('chart data exposes the aggregate under the key the views read', function (): void {
    expect(chartDataFor('user_rating'))->toHaveKey('primaryAggregate')
        ->and(chartDataFor('user_rating')['primaryAggregate'])->toBe(PrimaryAggregate::AVG);
});

/**
 * A label that differs from the default titleisation, so this can only pass if
 * the annotation was actually read.
 */
test('chart data prefers an annotated label over the titleised attribute name', function (): void {
    expect(chartDataFor('tickets_resolved_last_30_days')['label'])
        ->toBe('Tickets Resolved (Last 30 Days)');
});

test('chart data still falls back to defaults for an unannotated attribute', function (): void {
    $chart = chartDataFor('user_rating');

    expect($chart['chartType'])->toBe(ChartType::LINE);
});

/**
 * The workbench User annotates all nine of its tracked attributes. Before the
 * scanner could see modern accessors, none of the nine was ever read.
 */
test('every annotated workbench attribute now resolves its metadata', function (): void {
    $charts = collect(User::getStickleChartData());

    expect($charts)->toHaveCount(9);

    $unresolved = $charts
        ->filter(fn (array $chart): bool => $chart['description'] === null
            || $chart['dataType'] === null
            || $chart['primaryAggregate'] === null)
        ->pluck('key')
        ->all();

    expect($unresolved)->toBe([]);
});

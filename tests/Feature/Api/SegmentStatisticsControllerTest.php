<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Models\SegmentStatistic;

// Create mock segment class
class_alias(SegmentContract::class, 'StickleApp\\Segments\\TestSegment');

beforeEach(function (): void {
    // Set the segments namespace configuration
    Config::set('stickle.namespaces.segments', 'StickleApp\\Segments');
});

it('returns segment statistics data via API request', function (): void {
    // Create a segment
    $segment = Segment::query()->create([
        'name' => 'Test Segment',
        'description' => 'Test segment for statistics',
        'model_class' => 'User',
        'as_class' => 'TestSegment',
        'as_json' => '[]',
    ]);

    // Create some segment statistics
    SegmentStatistic::query()->create([
        'segment_id' => $segment->id,
        'attribute' => 'user_count',
        'value' => 100,
        'value_count' => 1,
        'value_sum' => 100,
        'value_min' => 100,
        'value_max' => 100,
        'value_avg' => 100.0,
        'recorded_at' => now()->subDays(3),
    ]);

    SegmentStatistic::query()->create([
        'segment_id' => $segment->id,
        'attribute' => 'user_count',
        'value' => 150,
        'value_count' => 1,
        'value_sum' => 150,
        'value_min' => 150,
        'value_max' => 150,
        'value_avg' => 150.0,
        'recorded_at' => now()->subDays(5),
    ]);

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'segment_id' => $segment->id,
        'attribute' => 'user_count',
    ]);

    $response = $this->getJson("/stickle/api/segment-statistics?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check response structure
    expect($data)->toHaveKeys(['time_series', 'delta', 'period']);
    expect($data['time_series'])->toBeArray();
    expect($data['time_series'])->toHaveCount(2);
    expect($data['delta'])->toBeArray();
    expect($data['period'])->toHaveKeys(['start', 'end']);
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Models\Segment;

// Create mock segment classes
class_alias(SegmentContract::class, 'StickleApp\\Segments\\Segment1');
class_alias(SegmentContract::class, 'StickleApp\\Segments\\Segment2');
class_alias(SegmentContract::class, 'StickleApp\\Segments\\Segment3');

beforeEach(function (): void {
    // Set the segments namespace configuration
    Config::set('stickle.namespaces.segments', 'StickleApp\\Segments');
});

it('returns segments data via API request', function (): void {
    // Create some segments
    $segments = collect([
        Segment::query()->create([
            'name' => 'Test Segment 1',
            'description' => 'Test segment 1 for testing',
            'model_class' => 'User',
            'as_class' => 'Segment1',
            'as_json' => '[]',
        ]),
        Segment::query()->create([
            'name' => 'Test Segment 2',
            'description' => 'Test segment 2 for testing',
            'model_class' => 'User',
            'as_class' => 'Segment2',
            'as_json' => '[]',
        ]),
        Segment::query()->create([
            'name' => 'Test Segment 3',
            'description' => 'Test segment 3 for testing',
            'model_class' => 'Customer',
            'as_class' => 'Segment3',
            'as_json' => '[]',
        ]),
    ]);

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'per_page' => 25,
    ]);

    $response = $this->getJson("/stickle/api/segments?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check pagination structure
    expect($data)->toHaveKeys(['data']);
    expect($data['data'])->toHaveCount(3);
    expect($data['total'])->toBe(3);
});

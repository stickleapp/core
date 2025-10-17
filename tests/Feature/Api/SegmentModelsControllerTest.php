<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use StickleApp\Core\Models\Segment;
use Workbench\App\Models\User;

// class_alias(SegmentContract::class, ' Workbench\\App\\Models\\User');

it('returns segment models data via API request', function (): void {
    // Create a segment
    $segment = Segment::query()->create([
        'name' => 'Test Segment',
        'description' => 'Test segment for testing',
        'model_class' => 'User',
        'as_class' => 'User',
        'as_json' => '[]',
    ]);

    // Create some users
    $users = User::factory()->count(3)->create();

    // Associate users with the segment via pivot table
    foreach ($users as $user) {
        DB::table(config('stickle.database.tablePrefix').'model_segment')->insert([
            'segment_id' => $segment->id,
            'object_uid' => (string) $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Make the API request with query parameters
    $queryParams = http_build_query([
        'segment_id' => $segment->id,
        'per_page' => 25,
    ]);

    $response = $this->getJson("/stickle/api/segment-models?{$queryParams}");

    // Assert basic response
    $response->assertOk();

    $data = $response->json();

    // Check pagination structure
    expect($data)->toHaveKeys(['data']);
    expect($data['data'])->toHaveCount(3);
    expect($data['total'])->toBe(3);
});

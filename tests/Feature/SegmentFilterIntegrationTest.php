<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function () {
    // Create test segments
    $this->activeSegment = SegmentModel::create([
        'name' => 'Active Users',
        'model_class' => 'User',
        'as_class' => 'ActiveUsers',
        'description' => 'Currently active users',
    ]);

    $this->vipSegment = SegmentModel::create([
        'name' => 'VIP Users',
        'model_class' => 'User',
        'as_class' => 'VipUsers',
        'description' => 'VIP status users',
    ]);

    // Create test users
    $this->activeUser = User::factory()->create(['name' => 'Active User']);
    $this->inactiveUser = User::factory()->create(['name' => 'Inactive User']);
    $this->vipUser = User::factory()->create(['name' => 'VIP User']);
    $this->regularUser = User::factory()->create(['name' => 'Regular User']);
});

test('segment isIn() filter finds current segment members', function () {
    $prefix = config('stickle.database.tablePrefix');

    // Insert current segment membership for active user
    DB::table($prefix.'model_segment')->insert([
        'object_uid' => (string) $this->activeUser->id,
        'segment_id' => $this->activeSegment->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $query = User::query()
        ->stickleWhere(
            Filter::segment('ActiveUsers')->isInSegment()
        );

    $sql = $query->toSql();

    // Verify SQL structure
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is not null');

    // Execute and verify results
    $users = $query->get();
    expect($users)->toHaveCount(1);
    expect($users->first()->name)->toBe('Active User');
});

test('segment isNotIn() filter finds non-members', function () {
    $prefix = config('stickle.database.tablePrefix');

    // Insert current segment membership for active user
    DB::table($prefix.'model_segment')->insert([
        'object_uid' => (string) $this->activeUser->id,
        'segment_id' => $this->activeSegment->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $query = User::query()
        ->stickleWhere(
            Filter::segment('ActiveUsers')->isNotInSegment()
        );

    $sql = $query->toSql();

    // Verify SQL structure
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('is null');

    // Execute and verify results - should find everyone except active user
    $users = $query->get();
    expect($users)->toHaveCount(3);
    expect($users->pluck('name')->toArray())->not()->toContain('Active User');
});

test('segmentHistory hasBeenIn() filter finds historical members', function () {
    $prefix = config('stickle.database.tablePrefix');

    // Insert historical segment entry for VIP user
    DB::table($prefix.'model_segment_audit')->insert([
        'object_uid' => (string) $this->vipUser->id,
        'segment_id' => $this->vipSegment->id,
        'operation' => 'ENTER',
        'recorded_at' => now()->subDays(30),
        'event_processed_at' => now()->subDays(30),
    ]);

    $query = User::query()
        ->stickleWhere(
            Filter::segmentHistory('VipUsers')->hasBeenInSegment()
        );

    $sql = $query->toSql();

    // Verify SQL structure
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment_audit');
    expect($sql)->toContain('is not null');
    expect($sql)->toContain('operation');

    // Execute and verify results
    $users = $query->get();
    expect($users)->toHaveCount(1);
    expect($users->first()->name)->toBe('VIP User');
});

test('segmentHistory hasNeverBeenIn() filter finds users who never were in segment', function () {
    $prefix = config('stickle.database.tablePrefix');

    // Insert historical segment entry for VIP user only
    DB::table($prefix.'model_segment_audit')->insert([
        'object_uid' => (string) $this->vipUser->id,
        'segment_id' => $this->vipSegment->id,
        'operation' => 'ENTER',
        'recorded_at' => now()->subDays(30),
        'event_processed_at' => now()->subDays(30),
    ]);

    $query = User::query()
        ->stickleWhere(
            Filter::segmentHistory('VipUsers')->hasNeverBeenInSegment()
        );

    $sql = $query->toSql();

    // Verify SQL structure
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment_audit');
    expect($sql)->toContain('is null');

    // Execute and verify results - should find everyone except VIP user
    $users = $query->get();
    expect($users)->toHaveCount(3);
    expect($users->pluck('name')->toArray())->not()->toContain('VIP User');
});

test('complex segment filter combinations work together', function () {
    $prefix = config('stickle.database.tablePrefix');

    // Setup data
    // Active user is currently in Active segment
    DB::table($prefix.'model_segment')->insert([
        'object_uid' => (string) $this->activeUser->id,
        'segment_id' => $this->activeSegment->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // VIP user was previously in VIP segment
    DB::table($prefix.'model_segment_audit')->insert([
        'object_uid' => (string) $this->vipUser->id,
        'segment_id' => $this->vipSegment->id,
        'operation' => 'ENTER',
        'recorded_at' => now()->subDays(30),
        'event_processed_at' => now()->subDays(30),
    ]);

    // Find users who are currently active AND have never been VIP
    $query = User::query()
        ->stickleWhere(
            Filter::segment('ActiveUsers')->isInSegment()
        )
        ->stickleWhere(
            Filter::segmentHistory('VipUsers')->hasNeverBeenInSegment()
        );

    $users = $query->get();

    // Should only find the active user (who is currently active but never VIP)
    expect($users)->toHaveCount(1);
    expect($users->first()->name)->toBe('Active User');
});

test('segment resolution by different identifiers', function () {
    // Test by name
    $queryByName = User::query()
        ->stickleWhere(Filter::segment('Active Users')->isInSegment());
    expect($queryByName->toSql())->toContain('model_segment');

    // Test by as_class
    $queryByClass = User::query()
        ->stickleWhere(Filter::segment('ActiveUsers')->isInSegment());
    expect($queryByClass->toSql())->toContain('model_segment');

    // Test by ID
    $queryById = User::query()
        ->stickleWhere(Filter::segment((string) $this->activeSegment->id)->isInSegment());
    expect($queryById->toSql())->toContain('model_segment');

    // All should generate equivalent queries
    expect($queryByName->toSql())->toBe($queryByClass->toSql());
    expect($queryByName->getBindings())->toBe($queryByClass->getBindings());
});

<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\SegmentHistory;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function () {
    // Create a test segment for use in tests
    $this->segment = SegmentModel::create([
        'name' => 'Test VIP Users',
        'model_class' => 'User',
        'as_class' => 'VipUsers',
        'description' => 'Users who were VIP at some point',
    ]);
});

test('segmentHistory() sets target as SegmentHistory', function () {
    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(SegmentHistory::class);
});

test('resolves segment by name', function () {
    $filter = Filter::segmentHistory('Test VIP Users');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('resolves segment by as_class', function () {
    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('resolves segment by numeric ID', function () {
    $filter = Filter::segmentHistory((string) $this->segment->id);
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('throws exception for non-existent segment', function () {
    $filter = Filter::segmentHistory('NonExistentSegment');
    $builder = User::query();

    expect(fn () => $filter->getTarget($builder))
        ->toThrow(\InvalidArgumentException::class, 'Segment not found: NonExistentSegment');
});

test('applies correct join with ENTER operation filter', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment_audit');
    expect($sql)->toContain('users.id::text');
    expect($sql)->toContain('object_uid');
    expect($sql)->toContain('operation');
});

test('property returns correct column', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->property())->toBe($prefix.'model_segment_audit.segment_id');
});

test('castProperty returns property without casting', function () {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe($prefix.'model_segment_audit.segment_id');
});

test('avoids duplicate joins', function () {
    $filter = Filter::segmentHistory('VipUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    // Apply join twice
    $target->applyJoin();
    $target->applyJoin();

    // Should only have one join
    $joins = $builder->getQuery()->joins ?? [];
    expect(count($joins))->toBe(1);
});

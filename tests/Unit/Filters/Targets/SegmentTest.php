<?php

declare(strict_types=1);

use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Filters\Targets\Segment;
use Workbench\App\Models\User;

beforeEach(function (): void {
    // Create a test segment for use in tests
    $this->segment = \StickleApp\Core\Models\Segment::query()->create([
        'name' => 'Test Active Users',
        'model_class' => 'User',
        'as_class' => 'ActiveUsers',
        'description' => 'Users who are currently active',
    ]);
});

test('segment() sets target as Segment', function (): void {
    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target)->toBeInstanceOf(Segment::class);
});

test('resolves segment by name', function (): void {
    $filter = Filter::segment('Test Active Users');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('resolves segment by as_class', function (): void {
    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('resolves segment by numeric ID', function (): void {
    $filter = Filter::segment((string) $this->segment->id);
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->segmentId)->toBe($this->segment->id);
});

test('throws exception for non-existent segment', function (): void {
    $filter = Filter::segment('NonExistentSegment');
    $builder = User::query();

    expect(fn () => $filter->getTarget($builder))
        ->toThrow(InvalidArgumentException::class, 'Segment not found: NonExistentSegment');
});

test('applies correct join', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    $target->applyJoin();

    $sql = $builder->toSql();
    expect($sql)->toContain('left join');
    expect($sql)->toContain($prefix.'model_segment');
    expect($sql)->toContain('users.id::text');
    expect($sql)->toContain('object_uid');
});

test('property returns correct column', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->property())->toBe($prefix.'model_segment.segment_id');
});

test('castProperty returns property without casting', function (): void {
    $prefix = config('stickle.database.tablePrefix');

    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    expect($target->castProperty())->toBe($prefix.'model_segment.segment_id');
});

test('avoids duplicate joins', function (): void {
    $filter = Filter::segment('ActiveUsers');
    $builder = User::query();
    $target = $filter->getTarget($builder);

    // Apply join twice
    $target->applyJoin();
    $target->applyJoin();

    // Should only have one join
    $joins = $builder->getQuery()->joins ?? [];
    expect(count($joins))->toBe(1);
});

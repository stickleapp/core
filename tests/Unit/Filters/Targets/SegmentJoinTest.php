<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

/**
 * A segment join is identified by its segment_id, which lives inside the join
 * condition rather than in the table name. Deduplicating on the table name
 * alone made a second, different segment reuse the first segment's join, so
 * both membership tests silently read the same segment.
 */
beforeEach(function (): void {
    $this->alpha = SegmentModel::query()->create([
        'name' => 'Alpha', 'model_class' => 'User', 'as_class' => 'Alpha', 'description' => 'a',
    ]);
    $this->beta = SegmentModel::query()->create([
        'name' => 'Beta', 'model_class' => 'User', 'as_class' => 'Beta', 'description' => 'b',
    ]);
    $this->prefix = config('stickle.database.tablePrefix');
});

function segmentJoinCount(string $sql, string $prefix): int
{
    return substr_count($sql, 'left join "'.$prefix.'model_segment"');
}

test('two different segments each get their own join', function (): void {
    $query = User::query()
        ->stickleWhere(Filter::segment('Alpha')->isInSegment())
        ->stickleOrWhere(Filter::segment('Beta')->isInSegment());

    expect(segmentJoinCount($query->toSql(), $this->prefix))->toBe(2)
        ->and($query->getBindings())->toContain($this->alpha->id)
        ->and($query->getBindings())->toContain($this->beta->id);
});

test('the same segment used twice is joined once', function (): void {
    $query = User::query()
        ->stickleWhere(Filter::segment('Alpha')->isInSegment())
        ->stickleOrWhere(Filter::segment('Alpha')->isNotInSegment());

    expect(segmentJoinCount($query->toSql(), $this->prefix))->toBe(1);
});

test('an or across two different segments returns the union of both', function (): void {
    $inAlpha = User::factory()->create(['name' => 'In Alpha']);
    $inBeta = User::factory()->create(['name' => 'In Beta']);
    User::factory()->create(['name' => 'In Neither']);

    DB::table($this->prefix.'model_segment')->insert([
        ['object_uid' => (string) $inAlpha->id, 'segment_id' => $this->alpha->id, 'created_at' => now(), 'updated_at' => now()],
        ['object_uid' => (string) $inBeta->id, 'segment_id' => $this->beta->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $users = User::query()
        ->stickleWhere(Filter::segment('Alpha')->isInSegment())
        ->stickleOrWhere(Filter::segment('Beta')->isInSegment())
        ->get();

    expect($users->pluck('name')->sort()->values()->all())->toBe(['In Alpha', 'In Beta']);
});

test('two different segment histories each get their own join', function (): void {
    $query = User::query()
        ->stickleWhere(Filter::segmentHistory('Alpha')->hasBeenInSegment())
        ->stickleOrWhere(Filter::segmentHistory('Beta')->hasBeenInSegment());

    $count = substr_count($query->toSql(), 'left join "'.$this->prefix.'model_segment_audit"');

    expect($count)->toBe(2)
        ->and($query->getBindings())->toContain($this->alpha->id)
        ->and($query->getBindings())->toContain($this->beta->id);
});

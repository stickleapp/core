<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use StickleApp\Core\Actions\SyncSegmentAction;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;
use Workbench\App\Segments\AllUsers;

beforeEach(function (): void {
    $this->prefix = config('stickle.database.tablePrefix');

    $this->segment = SegmentModel::query()->create([
        'name' => 'All Users',
        'model_class' => 'User',
        'as_class' => 'AllUsers',
        'description' => 'The users.',
    ]);

    $this->members = fn (): int => DB::table($this->prefix.'model_segment')
        ->where('segment_id', $this->segment->id)
        ->count();

    $this->audit = fn (string $operation): int => DB::table($this->prefix.'model_segment_audit')
        ->where('segment_id', $this->segment->id)
        ->where('operation', $operation)
        ->count();
});

it('populates membership from the segment query', function (): void {
    User::factory()->count(3)->create();

    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    expect(($this->members)())->toBe(3)
        ->and(($this->audit)('ENTER'))->toBe(3)
        ->and(($this->audit)('EXIT'))->toBe(0);
});

it('is idempotent and preserves created_at across re-runs', function (): void {
    User::factory()->count(3)->create();

    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    $createdAt = DB::table($this->prefix.'model_segment')
        ->where('segment_id', $this->segment->id)
        ->orderBy('object_uid')
        ->value('created_at');

    (new SyncSegmentAction)($this->segment->id, new AllUsers);
    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    $createdAtAfter = DB::table($this->prefix.'model_segment')
        ->where('segment_id', $this->segment->id)
        ->orderBy('object_uid')
        ->value('created_at');

    // Membership unchanged, entry timestamps untouched, and — critically — the
    // audit trigger did not emit a second round of ENTER events.
    expect(($this->members)())->toBe(3)
        ->and($createdAtAfter)->toEqual($createdAt)
        ->and(($this->audit)('ENTER'))->toBe(3)
        ->and(($this->audit)('EXIT'))->toBe(0);
});

it('removes members that no longer match and records a single exit', function (): void {
    $users = User::factory()->count(3)->create();

    (new SyncSegmentAction)($this->segment->id, new AllUsers);
    expect(($this->members)())->toBe(3);

    $users->first()->delete();

    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    expect(($this->members)())->toBe(2)
        ->and(($this->audit)('EXIT'))->toBe(1)
        ->and(($this->audit)('ENTER'))->toBe(3);
});

it('reconciles an empty segment to empty without error', function (): void {
    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    expect(($this->members)())->toBe(0);
});

it('does not wipe membership when the segment query yields no rows for another segment', function (): void {
    User::factory()->count(2)->create();

    // Distinct as_class only to satisfy the (model_class, as_class) unique
    // index — the action is handed its SegmentContract explicitly, so the
    // stored class name is irrelevant here. What matters is the segment_id.
    $otherSegmentId = SegmentModel::query()->create([
        'name' => 'Other',
        'model_class' => 'User',
        'as_class' => 'ActiveUsers',
        'description' => 'Other segment.',
    ])->id;

    (new SyncSegmentAction)($this->segment->id, new AllUsers);

    // Syncing a different segment must not touch this one's rows.
    (new SyncSegmentAction)($otherSegmentId, new AllUsers);

    expect(($this->members)())->toBe(2)
        ->and(($this->audit)('EXIT'))->toBe(0);
});

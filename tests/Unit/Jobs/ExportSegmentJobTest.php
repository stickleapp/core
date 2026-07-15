<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use StickleApp\Core\Actions\ExportSegmentAction;
use StickleApp\Core\Actions\SyncSegmentAction;
use StickleApp\Core\Jobs\ExportSegmentJob;
use StickleApp\Core\Jobs\ImportSegmentJob;
use StickleApp\Core\Models\Segment as SegmentModel;
use Workbench\App\Models\User;

beforeEach(function (): void {
    Bus::fake();
    Storage::fake('local');

    $this->prefix = config('stickle.database.tablePrefix');

    $this->segment = SegmentModel::query()->create([
        'name' => 'All Users',
        'model_class' => 'User',
        'as_class' => 'AllUsers',
        'description' => 'The users.',
    ]);

    $this->handle = fn () => (new ExportSegmentJob($this->segment))->handle(
        resolve(ExportSegmentAction::class),
        resolve(SyncSegmentAction::class),
    );
});

it('reconciles in-database and dispatches no import job by default', function (): void {
    config()->set('stickle.segments.useCsvExports', false);

    User::factory()->count(2)->create();

    ($this->handle)();

    Bus::assertNotDispatched(ImportSegmentJob::class);

    expect(DB::table($this->prefix.'model_segment')
        ->where('segment_id', $this->segment->id)
        ->count())->toBe(2);
});

it('writes no export file by default', function (): void {
    config()->set('stickle.segments.useCsvExports', false);
    config()->set('stickle.filesystem.disks.exports', 'local');

    User::factory()->count(2)->create();

    ($this->handle)();

    // The SQL path touches no filesystem at all — this is what removes the
    // shared-disk requirement on multi-instance hosts.
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('falls back to the CSV round trip when useCsvExports is enabled', function (): void {
    config()->set('stickle.segments.useCsvExports', true);
    config()->set('stickle.filesystem.disks.exports', 'local');

    User::factory()->count(2)->create();

    ($this->handle)();

    Bus::assertDispatched(ImportSegmentJob::class);

    // The CSV path defers the write to ImportSegmentJob, so membership is not
    // reconciled synchronously the way the SQL path does.
    expect(DB::table($this->prefix.'model_segment')
        ->where('segment_id', $this->segment->id)
        ->count())->toBe(0);
});

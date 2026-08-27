<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Jobs\RecordModelAttributesJob;
use Workbench\App\Models\User;

/**
 * The command left-joins stc_model_attributes to read each row's synced_at and
 * skip anything refreshed inside the configured interval. The join binds
 * model_class, and every writer stores a basename -- so binding the fully
 * qualified name matches nothing, synced_at comes back NULL for every row, and
 * the guard's orWhereNull branch makes it unconditionally true.
 *
 * Nothing covered this, so a guard that never suppressed anything looked
 * exactly like a guard that worked. The command is scheduled every five
 * minutes against a 360-minute default.
 */
function writeAttributeRow(User $user, ?string $syncedAt): void
{
    // Creating the model already writes a row through the StickleEntity
    // observer, so this sets synced_at on that row rather than adding another.
    DB::table(config('stickle.database.tablePrefix').'model_attributes')->updateOrInsert(
        ['model_class' => 'User', 'object_uid' => (string) $user->getKey()],
        ['data' => json_encode(['user_rating' => 3]), 'synced_at' => $syncedAt, 'updated_at' => now(), 'created_at' => now()]
    );
}

test('a model synced inside the interval is not re-queued', function (): void {
    Bus::fake();

    $user = User::factory()->create();
    writeAttributeRow($user, now()->subMinutes(5)->toDateTimeString());

    $this->artisan('stickle:record-model-attributes')->assertSuccessful();

    Bus::assertNotDispatched(
        RecordModelAttributesJob::class,
        fn (RecordModelAttributesJob $recordModelAttributesJob): bool => $recordModelAttributesJob->stickleEntity->is($user)
    );
});

test('a model synced longer ago than the interval is re-queued', function (): void {
    Bus::fake();

    $user = User::factory()->create();
    writeAttributeRow($user, now()->subMinutes(600)->toDateTimeString());

    $this->artisan('stickle:record-model-attributes')->assertSuccessful();

    Bus::assertDispatched(fn (RecordModelAttributesJob $recordModelAttributesJob): bool => $recordModelAttributesJob->stickleEntity->is($user));
});

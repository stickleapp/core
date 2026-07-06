<?php

use Illuminate\Console\Scheduling\Schedule;
use StickleApp\Core\ScheduleServiceProvider;

test('schedules create and drop partition jobs for every managed table', function (): void {
    // The scheduler reads these with no fallback, so they must be present.
    config()->set('stickle.database.tablePrefix', 'stc_');
    config()->set('stickle.database.schema', 'public');

    $managed = [
        'requests',
        'sessions',
        'model_attribute_audit',
        'segment_statistics',
        'model_relationship_statistics',
    ];

    foreach ($managed as $group) {
        config()->set("stickle.database.partitions.{$group}.interval", 'week');
        config()->set("stickle.database.partitions.{$group}.extension", '1 week');
        config()->set("stickle.database.partitions.{$group}.retention", '1 years');
    }

    // Ensure Schedule is resolved, then register the partition jobs onto it.
    $schedule = resolve(Schedule::class);
    (new ReflectionMethod(ScheduleServiceProvider::class, 'schedulePartitionJobs'))
        ->invoke(app()->getProvider(ScheduleServiceProvider::class));

    $commands = collect($schedule->events())->map(fn ($event): string => (string) $event->command);

    $hasJob = fn (string $action, string $table): bool => $commands->contains(
        fn (string $command): bool => str_contains($command, "stickle:{$action}-partitions")
            && str_contains($command, "stc_{$table}")
    );

    // The three statistics/audit tables must now have both create and drop jobs.
    foreach (['model_attribute_audit', 'segment_statistics', 'model_relationship_statistics'] as $table) {
        expect($hasJob('create', $table))->toBeTrue("missing create-partitions job for {$table}")
            ->and($hasJob('drop', $table))->toBeTrue("missing drop-partitions job for {$table}");
    }
});

<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

test('Command Exists', function (): void {
    $this->artisan("stickle:create-partitions {$this->tablePrefix}requests_rollup_1min public week '2024-08-01'")->assertExitCode(0);
});

test('creates the current period partition when period_start is in the future', function (): void {
    // Fresh parent table with no partitions, isolated from the harness pre-seeding.
    $parent = 'test_partition_current_period';

    DB::unprepared("DROP TABLE IF EXISTS {$parent} CASCADE");
    DB::unprepared("CREATE TABLE {$parent} (id bigint, \"timestamp\" timestamptz NOT NULL) PARTITION BY RANGE (\"timestamp\")");

    // Simulate the scheduler: period_start is one interval in the future (now + extension).
    $future = now()->addWeek()->format('Y-m-d');

    $this->artisan("stickle:create-partitions {$parent} public week '{$future}' 1")
        ->assertExitCode(0);

    $partitionExists = fn (Carbon $weekStart): bool => DB::table('information_schema.tables')
        ->where('table_schema', 'public')
        ->where('table_name', sprintf('%s_week_%s', $parent, $weekStart->format('YmdHis')))
        ->exists();

    // The partition covering "now" (the current ISO week) must exist so inserts don't fail.
    $currentExists = $partitionExists(now()->startOfWeek(Carbon::MONDAY));

    // The requested future partition (next week) must STILL be created.
    $futureExists = $partitionExists(now()->addWeek()->startOfWeek(Carbon::MONDAY));

    DB::unprepared("DROP TABLE IF EXISTS {$parent} CASCADE");

    expect($currentExists)->toBeTrue()
        ->and($futureExists)->toBeTrue();
});

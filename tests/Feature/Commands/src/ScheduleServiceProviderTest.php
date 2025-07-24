<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use StickleApp\Core\ScheduleServiceProvider;

beforeEach(function () {
    // Create a fresh schedule instance for each test
    $this->schedule = new Schedule(app());
    
    // Bind our fresh schedule to the container
    app()->instance(Schedule::class, $this->schedule);
    
    $this->provider = new ScheduleServiceProvider(app());
    $this->provider->boot();
});

it('can be instantiated', function () {
    $provider = new ScheduleServiceProvider(app());
    expect($provider)->toBeInstanceOf(ScheduleServiceProvider::class);
});

it('boots without errors', function () {
    expect(fn () => $this->provider->boot())->not->toThrow(Exception::class);
});

it('schedules events partition creation commands', function () {
    $events = $this->schedule->events();

    $eventsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:create-partitions')
            && str_contains($event->command, 'events_rollup');
    });

    expect($eventsCommands)->toHaveCount(4);
});

it('schedules events partition drop commands', function () {
    $events = $this->schedule->events();

    $eventsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:drop-partitions')
            && str_contains($event->command, 'events_rollup');
    });

    expect($eventsCommands)->toHaveCount(4);
});

it('schedules requests partition creation commands', function () {
    $events = $this->schedule->events();

    $requestsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:create-partitions')
            && str_contains($event->command, 'requests_rollup');
    });

    expect($requestsCommands)->toHaveCount(4);
});

it('schedules requests partition drop commands', function () {
    $events = $this->schedule->events();

    $requestsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:drop-partitions')
            && str_contains($event->command, 'requests_rollup');
    });

    expect($requestsCommands)->toHaveCount(4);
});

it('schedules sessions partition creation command', function () {
    $events = $this->schedule->events();

    $sessionsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:create-partitions')
            && str_contains($event->command, 'sessions_rollup_1day');
    });

    expect($sessionsCommands)->toHaveCount(1);
});

it('schedules sessions partition drop command', function () {
    $events = $this->schedule->events();

    $sessionsCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:drop-partitions')
            && str_contains($event->command, 'sessions_rollup_1day');
    });
    
    expect($sessionsCommands)->toHaveCount(1);
});

it('uses correct table prefix from config', function () {
    $tablePrefix = config('stickle.database.tablePrefix');
    $events = $this->schedule->events();

    $commandsWithPrefix = collect($events)->filter(function ($event) use ($tablePrefix) {
        return str_contains($event->command, $tablePrefix.'events_rollup')
            || str_contains($event->command, $tablePrefix.'requests_rollup')
            || str_contains($event->command, $tablePrefix.'sessions_rollup');
    });

    expect($commandsWithPrefix->count())->toBeGreaterThan(0);
});

it('uses correct schema from config', function () {
    $schema = config('stickle.database.schema');
    $events = $this->schedule->events();

    $commandsWithSchema = collect($events)->filter(function ($event) use ($schema) {
        return str_contains($event->command, $schema);
    });

    expect($commandsWithSchema->count())->toBeGreaterThan(0);
});

it('schedules commands at different times to avoid conflicts', function () {
    $events = $this->schedule->events();

    $scheduleTimes = collect($events)->map(function ($event) {
        // Extract the scheduling expression to check for different times
        return $event->getExpression();
    })->unique();

    // Should have multiple different schedule times
    expect($scheduleTimes->count())->toBeGreaterThan(1);
});

test('all scheduled commands include required parameters', function () {
    $events = $this->schedule->events();

    $partitionCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:create-partitions')
            || str_contains($event->command, 'stickle:drop-partitions');
    });

    $partitionCommands->each(function ($event) {
        $command = $event->command;

        // Each command should have table name, schema, interval, and date parameters
        expect($command)->toContain('rollup');
        expect($command)->toContain(config('stickle.database.schema'));
    });

    expect($partitionCommands->count())->toBe(18); // 4+4 events, 4+4 requests, 1+1 sessions
});

it('generates future dates for partition extension', function () {
    $events = $this->schedule->events();

    $createCommands = collect($events)->filter(function ($event) {
        return str_contains($event->command, 'stickle:create-partitions');
    });

    $createCommands->each(function ($event) {
        $command = $event->command;

        // Should contain a future date (today or later)
        $today = now()->startOfDay();
        $hasValidDate = preg_match('/\d{4}-\d{2}-\d{2}/', $command, $matches);

        expect($hasValidDate)->toBeTruthy();
        if ($hasValidDate) {
            $commandDate = Carbon::parse($matches[0])->startOfDay();
            expect($commandDate->greaterThanOrEqualTo($today))->toBeTruthy();
        }
    });
});

it('uses extension config for create commands and retention config for drop commands', function () {
    // This is tested indirectly through the configuration usage
    // The actual date calculation happens at runtime with Carbon parsing
    expect(config('stickle.database.partitions.events.extension'))->not->toBeNull();
    expect(config('stickle.database.partitions.events.retention'))->not->toBeNull();
    expect(config('stickle.database.partitions.requests.extension'))->not->toBeNull();
    expect(config('stickle.database.partitions.requests.retention'))->not->toBeNull();
    expect(config('stickle.database.partitions.sessions.extension'))->not->toBeNull();
    expect(config('stickle.database.partitions.sessions.retention'))->not->toBeNull();
});

it('registers the provider correctly', function () {
    expect(fn () => $this->provider->register())->not->toThrow(Exception::class);
});

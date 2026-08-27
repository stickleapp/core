<?php

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Commands\CreatePartitionsCommand;
use StickleApp\Core\Commands\InstallCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

test('createInitialPartitions seeds current and future partitions for maintained tables', function (): void {
    // Boot the command enough to allow $this->call() from within the private method.
    $console = new ConsoleApplication(app(), resolve(Dispatcher::class), app()->version());
    $console->resolveCommands([CreatePartitionsCommand::class]);

    $installCommand = resolve(InstallCommand::class);
    $installCommand->setLaravel(app());
    $installCommand->setApplication($console);
    $installCommand->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));
    (new ReflectionProperty(Command::class, 'input'))
        ->setValue($installCommand, new ArrayInput([]));

    (new ReflectionMethod($installCommand, 'createInitialPartitions'))->invoke($installCommand);

    $partitionExists = fn (string $table, Carbon $weekStart): bool => DB::table('information_schema.tables')
        ->where('table_schema', 'public')
        ->where('table_name', sprintf('%s_week_%s', $table, $weekStart->format('YmdHis')))
        ->exists();

    $currentWeek = now()->startOfWeek(Carbon::MONDAY);
    $nextWeek = now()->addWeek()->startOfWeek(Carbon::MONDAY);

    // Every maintained partitioned table (request rollups + the statistics/audit
    // tables) must have both the current partition and the future (extension) one.
    $tables = [
        'requests',
        'requests_rollup_1min',
        'sessions_rollup_1day',
        'model_attribute_audit',
        'segment_statistics',
        'model_relationship_statistics',
    ];

    foreach ($tables as $base) {
        $table = $this->tablePrefix.$base;

        // Current week must be covered (the whole point of seeding at install time).
        expect($partitionExists($table, $currentWeek))->toBeTrue()
            // ...and the future (extension) partition the harness did not pre-create.
            ->and($partitionExists($table, $nextWeek))->toBeTrue();
    }
});

test('formatSettings writes each answer into the env key it belongs to', function (): void {
    $installCommand = resolve(InstallCommand::class);

    $formatted = (new ReflectionMethod($installCommand, 'formatSettings'))->invoke($installCommand, [
        'interval' => 15,
        'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE' => true,
        'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED' => true,
    ]);

    // The interval answer fans out to all four scheduling frequencies. The
    // relationship-statistics key was the one previously missing, and the
    // client-middleware key was overwritten with 15 in its place.
    expect($formatted)
        ->toHaveKey('STICKLE_FREQUENCY_EXPORT_SEGMENTS', 15)
        ->toHaveKey('STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES', 15)
        ->toHaveKey('STICKLE_FREQUENCY_RECORD_MODEL_RELATIONSHIP_STATISTICS', 15)
        ->toHaveKey('STICKLE_FREQUENCY_RECORD_SEGMENT_STATISTICS', 15)
        ->and($formatted)->not->toHaveKey('interval');

    // The client middleware answer survives as the boolean CoreServiceProvider
    // compares with ===, rather than becoming the authentication event list.
    expect($formatted['STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE'])->toBeTrue();

    // config/stickle.php explodes this key on commas, so the boolean answer has
    // to be expanded into the list before it is written. What gets written is
    // the recommended default, which is not the full set: Authenticated and
    // Validated fire per request and per credential check, so they are opt-in.
    expect($formatted['STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED'])
        ->toContain('Login')
        ->toContain('Registered')
        ->and(explode(',', (string) $formatted['STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED']))
        ->not->toContain('Authenticated')
        ->not->toContain('Validated')
        ->toHaveCount(7);
});

test('formatSettings disables authentication tracking when declined', function (): void {
    $installCommand = resolve(InstallCommand::class);

    $formatted = (new ReflectionMethod($installCommand, 'formatSettings'))->invoke($installCommand, [
        'interval' => 360,
        'STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE' => false,
        'STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED' => false,
    ]);

    expect($formatted['STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED'])->toBe('')
        ->and($formatted['STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE'])->toBeFalse();
});

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

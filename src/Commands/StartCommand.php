<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

final class StartCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'laravel-cascade:start {--withWebsocketServer} {--withDbListener}';

    /**
     * @var string
     */
    protected $description = 'Start the Cascade process. Includes two options: --withWebsocketServer will start Reverb if available, --withDbListener will listen to changes in your database to trigger updates.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void {}
}

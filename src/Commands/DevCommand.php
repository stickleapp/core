<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DevCommand extends Command
{
    protected $signature = 'stickle:dev';

    protected $description = 'Start development services';

    public function handle()
    {
        $processes = [
            new Process(['php', 'artisan', 'schedule:work']),
            new Process(['php', 'artisan', 'reverb:start']),
        ];

        foreach ($processes as $process) {
            $process->start();
            $this->info('Started: '.$process->getCommandLine());
        }

        // Keep the command running
        while (true) {
            foreach ($processes as $process) {
                if (! $process->isRunning()) {
                    $this->error('Process died: '.$process->getCommandLine());

                    return 1;
                }
            }
            sleep(1);
        }
    }
}

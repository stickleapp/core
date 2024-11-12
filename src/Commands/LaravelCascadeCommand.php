<?php

namespace Dclaysmith\LaravelCascade\Commands;

use Illuminate\Console\Command;

class LaravelCascadeCommand extends Command
{
    public $signature = 'laravel-cascade';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

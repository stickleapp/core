<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

final class ServeCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'laravel-cascade:serve';

    /**
     * @var string
     */
    protected $description = 'A convenience method for demonstrating Cascade functionality. Opens a local webserver displaying: your webapp on a test page (dev only), an admin interface test page (dev only) and an example 3rd-party app (ex. SnailsForce.com). Perhaps with a console view.';

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

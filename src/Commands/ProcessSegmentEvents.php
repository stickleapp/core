<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

final class ProcessSegmentEvents extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'cascade:process-segment-events';

    /**
     * @var string
     */
    protected $description = 'Triggger events for new inserts/deletes into the object_segment table.';

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
    public function handle(): void
    {

        /**
         * Retrieve the
         **/
    }
}

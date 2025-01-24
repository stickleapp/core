<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        /**
         * Schedule the creation of partitions
         */
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:create-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1min',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(1, 8, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:create-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_5min',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(3, 15, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:create-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1hr',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(5, 17, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:create-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1day',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(7, 19, 0);
        });

        /**
         * Schedule the dropping of partitions
         */
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:drop-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1min',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(1, 8, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:drop-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_5min',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(3, 15, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:drop-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1hr',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(5, 17, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'STICKLE:drop-partitions',
                [
                    config('stickle.database.tablePrefix').'events_rollup_1day',
                    config('stickle.database.schema'),
                    config('stickle.database.partitions.interval'),
                    now()->add(Carbon::parse(config('stickle.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(7, 19, 0);
        });
    }

    public function register() {}
}

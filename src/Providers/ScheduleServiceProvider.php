<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {

        /**
         * Schedule the creation of partitions
         */
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:create-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1min',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(1, 8, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:create-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_5min',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(3, 15, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:create-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1hr',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.events.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(5, 17, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:create-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1day',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.extension'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(7, 19, 0);
        });

        /**
         * Schedule the dropping of partitions
         */
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:drop-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1min',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(1, 8, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:drop-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_5min',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(3, 15, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:drop-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1hr',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(5, 17, 0);
        });

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(
                'cascade:drop-partitions',
                [
                    config('cascade.database.tablePrefix').'events_rollup_1day',
                    config('cascade.database.schema'),
                    config('cascade.database.partitions.interval'),
                    now()->add(Carbon::parse(config('cascade.database.partitions.events.retention'))->diffAsCarbonInterval())->format('Y-m-d'),
                ]
            )->twiceDailyAt(7, 19, 0);
        });
    }

    public function register() {}
}

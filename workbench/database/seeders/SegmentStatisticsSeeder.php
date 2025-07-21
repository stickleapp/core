<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SegmentStatisticsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        $startDate = now()->subDays(25);
        $endDate = now();

        Artisan::call("stickle:create-partitions {$prefix}segment_statistics public week '{$startDate->toDateString()}' 2");

        // Get all segments with their model information
        $segments = \DB::table("{$prefix}segments")
            ->select('id', 'model_class')
            ->get();

        foreach ($segments as $segment) {
            $this->populateSegmentStatistics($segment, $startDate, $endDate, $prefix);
        }
    }

    private function populateSegmentStatistics($segment, $startDate, $endDate, $prefix): void
    {
        // Get model class and trackable attributes
        $modelClass = config('stickle.namespaces.models').'\\'.$segment->model_class;

        if (! class_exists($modelClass)) {
            return;
        }

        $model = new $modelClass;
        $trackedAttributes = $model::$stickleTrackedAttributes ?? [];

        if (empty($trackedAttributes)) {
            return;
        }

        // Get models in this segment
        $modelIds = \DB::table("{$prefix}model_segment")
            ->where('segment_id', $segment->id)
            ->pluck('object_uid')
            ->toArray();

        if (empty($modelIds)) {
            return;
        }

        // Generate statistics for each day and each attribute
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            foreach ($trackedAttributes as $attribute) {
                $this->generateStatisticForDay($segment->id, $attribute, $modelIds, $currentDate, $prefix);
            }
            $currentDate->addDay();
        }
    }

    private function generateStatisticForDay($segmentId, $attribute, $modelIds, $date, $prefix): void
    {
        // Generate realistic statistics with gradual changes
        $baseValue = $this->getBaseValueForAttribute($attribute);
        $daysSinceStart = $date->diffInDays(now()->subDays(25));

        // Add gradual trend (slight increase over time for most metrics)
        $trendMultiplier = 1 + ($daysSinceStart * 0.02); // 2% increase per day

        // Add some randomness for realism
        $randomVariation = 1 + (mt_rand(-20, 20) / 100); // ±20% variation

        $count = count($modelIds);
        $min = max(0, $baseValue * 0.3 * $randomVariation);
        $max = $baseValue * 2 * $trendMultiplier * $randomVariation;
        $avg = $baseValue * $trendMultiplier * $randomVariation;
        $sum = $avg * $count;

        // Check if record already exists
        $exists = \DB::table("{$prefix}segment_statistics")
            ->where('segment_id', $segmentId)
            ->where('attribute', $attribute)
            ->where('recorded_at', $date->toDateString())
            ->exists();

        if (! $exists) {
            \DB::table("{$prefix}segment_statistics")->insert([
                'segment_id' => $segmentId,
                'attribute' => $attribute,
                'value' => $avg,
                'value_avg' => $avg,
                'value_count' => $count,
                'value_max' => $max,
                'value_min' => $min,
                'value_sum' => $sum,
                'recorded_at' => $date->toDateString(),
            ]);
        }
    }

    private function getBaseValueForAttribute($attribute): float
    {
        // Return realistic base values for different attribute types
        return match ($attribute) {
            'user_rating' => 3.5,
            'ticket_count' => 12,
            'open_ticket_count' => 3,
            'closed_ticket_count' => 9,
            'tickets_closed_last_30_days' => 8,
            'average_resolution_time' => 3600, // 1 hour in seconds
            'average_resolution_time_30_days' => 3200,
            'mrr' => 99, // Monthly recurring revenue
            'tickets_resolved_last_7_days' => 2,
            'tickets_resolved_last_30_days' => 8,
            'average_resolution_time_7_days' => 3000,
            default => 10, // Default base value
        };
    }
}

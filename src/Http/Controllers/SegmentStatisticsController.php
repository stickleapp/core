<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Models\Segment;

class SegmentStatisticsController
{
    public function index(Request $request): JsonResponse
    {
        config('stickle.database.tablePrefix');

        $request->string('attribute');

        $segmentId = $request->integer('segment_id');

        $segment = Segment::query()->findOrFail($segmentId);

        // Date ranges
        $currentPeriodStart = $request->date('date_from') ?? now()->subDays(30);
        $currentPeriodEnd = $request->date('date_to') ?? now();

        $statisticsEntries = $segment->segmentStatistics()
            ->where('attribute', $request->string('attribute'))
            ->where('recorded_at', '>=', $currentPeriodStart->startOfDay())
            ->where('recorded_at', '<', $currentPeriodEnd->endOfDay())
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Get the first and last values in the period (for calculating change)
        $firstEntry = $statisticsEntries->first();
        $lastEntry = $statisticsEntries->last();

        // Calculate change over 30 days
        $changeData = null;
        if ($firstEntry && $lastEntry) {

            $firstValue = $firstEntry->value_avg ? (float) $firstEntry->value_avg : null;
            $lastValue = $lastEntry->value_avg ? (float) $lastEntry->value_avg : null;

            if ($firstValue !== null && $lastValue !== null) {
                $absoluteChange = $lastValue - $firstValue;
                $percentageChange = $firstValue != 0 ? ($absoluteChange / $firstValue) * 100 : null;

                $changeData = [
                    'start_value' => $firstValue,
                    'end_value' => $lastValue,
                    'absolute_change' => $absoluteChange,
                    'percentage_change' => $percentageChange !== null ? round($percentageChange, 2) : null,
                    'start_date' => $firstEntry->recorded_at,
                    'end_date' => $lastEntry->recorded_at,
                ];
            }
        }

        // Add time-series data points for visualization
        $timeSeriesData = $statisticsEntries->map(fn($entry): array => [
            'timestamp' => $entry->recorded_at,
            'value' => $entry->value_avg ? (float) $entry->value_avg : null,
        ]);

        $response = [
            'time_series' => $timeSeriesData,
            'delta' => $changeData,
            'period' => [
                'start' => $currentPeriodStart->toDateTimeString(),
                'end' => $currentPeriodEnd->toDateTimeString(),
            ],
        ];

        return response()->json($response);
    }
}

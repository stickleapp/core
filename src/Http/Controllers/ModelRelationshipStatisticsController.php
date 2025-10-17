<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

class ModelRelationshipStatisticsController
{
    public function index(Request $request): JsonResponse
    {
        config('stickle.database.tablePrefix');

        $attribute = $request->string('attribute')->toString();

        $modelClass = $request->string('model_class')->toString();

        $relationship = $request->string('relationship')->toString();

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst((string) $modelClass);

        if (! class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, StickleEntity::class)) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        $model = $modelClass::findOrFail($request->string('uid')->toString());

        // Date ranges
        $currentPeriodEnd = now();
        $currentPeriodStart = now()->subDays(30);

        $statisticsEntries = $model->modelRelationshipStatistics()
            ->where('attribute', $request->string('attribute'))
            ->where('relationship', $relationship)
            ->where('recorded_at', '>=', $currentPeriodStart)
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Get the first and last values in the period (for calculating change)
        $firstEntry = $statisticsEntries->first();
        $lastEntry = $statisticsEntries->last();

        // Calculate change over 30 days
        $changeData = null;
        if ($firstEntry && $lastEntry) {
            $firstValue = is_numeric($firstEntry->value_avg) ? (float) $firstEntry->value_avg : null;
            $lastValue = is_numeric($lastEntry->value_avg) ? (float) $lastEntry->value_avg : null;

            if ($firstValue !== null && $lastValue !== null) {
                $absoluteChange = $lastValue - $firstValue;
                $percentageChange = $firstValue != 0 ? ($absoluteChange / $firstValue) * 100 : null;

                $changeData = [
                    'start_value' => $firstValue,
                    'end_value' => $lastValue,
                    'absolute_change' => $absoluteChange,
                    'percentage_change' => $percentageChange !== null ? round($percentageChange, 2) : null,
                    'start_date' => $firstEntry->timestamp,
                    'end_date' => $lastEntry->timestamp,
                ];
            }
        }

        // Add time-series data points for visualization
        $timeSeriesData = $statisticsEntries->map(fn ($entry): array => [
            'timestamp' => $entry->recorded_at,
            'value' => is_numeric($entry->value_avg) ? (float) $entry->value_avg : null,
        ]);

        // Assemble the response
        $response = [
            'value' => $model->getAttribute($attribute),
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

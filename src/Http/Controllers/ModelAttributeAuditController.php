<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

class ModelAttributeAuditController
{
    public function index(Request $request): JsonResponse
    {

        Log::debug('ModelAttributeAuditController', [
            $request->getContent(),
        ]);

        $request->validate([
            'uid' => ['required', 'string'],
            'attribute' => ['required', 'string'],
            'model_class' => ['required', 'string'],
        ]);

        $request->string('attribute');

        $modelClass = config('stickle.namespaces.models').'\\'.
            $request->string('model_class');

        if (! class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, StickleEntity::class)) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        $model = $modelClass::findOrFail($request->string('uid'));

        // Date ranges
        $currentPeriodEnd = now();
        $currentPeriodStart = now()->subDays(30);

        // Get the attribute audit history for the last 30 days
        $auditEntries = $model->modelAttributeAudits()
            ->where('attribute', $request->string('attribute'))
            ->where('timestamp', '>=', $currentPeriodStart)
            ->orderBy('timestamp', 'asc')
            ->select('timestamp')
            ->selectRaw('value_new as value')
            ->get();

        // Get the first and last values in the period (for calculating change)
        $firstEntry = $auditEntries->first();
        $lastEntry = $auditEntries->last();

        // Calculate change over 30 days
        $changeData = null;
        if ($firstEntry && $lastEntry) {
            $firstValue = is_numeric($firstEntry->value) ? (float) $firstEntry->value : null;
            $lastValue = is_numeric($lastEntry->value) ? (float) $lastEntry->value : null;

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
        $timeSeriesData = $auditEntries->map(fn ($entry): array => [
            'timestamp' => $entry->timestamp,
            'value' => is_numeric($entry->value) ? (float) $entry->value : null,
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

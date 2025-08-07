<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Http\Controllers\Requests\ActivitiesIndexRequest;

class ActivitiesController
{
    public function index(ActivitiesIndexRequest $request): JsonResponse
    {
        $prefix = config('stickle.database.tablePrefix');

        // Build base query for activities (union of requests and events)
        $requestsQuery = DB::table("{$prefix}requests")
            ->leftJoin("{$prefix}location_data", "{$prefix}requests.ip_address", '=', "{$prefix}location_data.ip_address")
            ->select([
                DB::raw("'page_view' as activity_type"),
                'model_class',
                'object_uid',
                'session_uid',
                'timestamp',
                "{$prefix}requests.ip_address",
                DB::raw("JSONB_OBJECT(ARRAY['url'], ARRAY[url]) as properties"),
            ])
            ->whereBetween('timestamp', [$request->string('start_at'), $request->string('end_at')])
            ->when($request->string('model_class'), function ($query) use ($request) {
                return $query->where('model_class', $request->string('model_class'));
            })->when($request->string('object_uid'), function ($query) use ($request) {
                return $query->where('object_uid', $request->string('object_uid'));
            });

        $eventsQuery = DB::table("{$prefix}events")
            ->leftJoin("{$prefix}location_data", "{$prefix}events.ip_address", '=', "{$prefix}location_data.ip_address")
            ->select([
                DB::raw("'event' as activity_type"),
                'model_class',
                'object_uid',
                'session_uid',
                'timestamp',
                "{$prefix}events.ip_address",
                'properties',
            ])
            ->whereBetween('timestamp', [$request->string('start_at'), $request->string('end_at')])
            ->when($request->string('model_class'), function ($query) use ($request) {
                return $query->where('model_class', $request->string('model_class'));
            })->when($request->string('object_uid'), function ($query) use ($request) {
                return $query->where('object_uid', $request->string('object_uid'));
            });

        $unionQuery = $requestsQuery->union($eventsQuery);

        // Order by timestamp and limit
        $activities = $unionQuery
            ->orderBy('timestamp', 'desc')
            ->limit($request->integer('per_page', 100))
            ->get();

        // Transform the results
        $transformedActivities = $activities->map(function ($activity) use ($request) {
            $result = [
                'id' => $activity->id,
                'model' => $this->getModelData($activity->model_class, $activity->object_uid),
                'activity_type' => $activity->activity_type,
                'properties' => json_decode($activity->properties, true),
                'session_status' => $this->getSessionStatus($activity->model_class, $activity->object_uid),
            ];

            if ($request->boolean('include_location') && $activity->city) {
                $result['location'] = [
                    'city' => $activity->city,
                    'country' => $activity->country,
                    'lat' => (float) $activity->lat,
                    'lng' => (float) $activity->lng,
                ];
            }

            return $result;
        });

        return response()->json([
            'data' => $transformedActivities,
        ]);
    }

    /**
     * @return array<string, mixed> Model data
     */
    private function getModelData(string $modelClass, string $objectUid): array
    {
        // TODO: Implement dynamic model resolution and data fetching
        // This should resolve the model class and fetch basic model data
        return [
            'name' => 'Unknown User',
            'email' => null,
            'customer_name' => null,
            'user_type' => null,
        ];
    }

    private function getSessionStatus(string $modelClass, string $objectUid): string
    {
        // Check if user has activity within last 30 minutes
        $recentActivity = DB::table(config('stickle.tables.requests'))
            ->where('model_class', $modelClass)
            ->where('object_uid', $objectUid)
            ->where('timestamp', '>=', now()->subMinutes(30))
            ->exists();

        if (! $recentActivity) {
            $recentActivity = DB::table(config('stickle.tables.events'))
                ->where('model_class', $modelClass)
                ->where('object_uid', $objectUid)
                ->where('timestamp', '>=', now()->subMinutes(30))
                ->exists();
        }

        return $recentActivity ? 'active' : 'inactive';
    }
}

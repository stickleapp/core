<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Support\Facades\DB;
use StickleApp\Core\Http\Controllers\Requests\ActivitiesIndexRequest;
use StickleApp\Core\Http\Controllers\Resources\ActivityCollection;

class ActivitiesController
{
    public function index(ActivitiesIndexRequest $request): ActivityCollection
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
                DB::raw("JSONB_OBJECT(
                    ARRAY['city', 'country', 'coordinates'],
                    ARRAY[{$prefix}location_data.city, {$prefix}location_data.country, {$prefix}location_data.coordinates::text]
                ) as location"),
                DB::raw("JSONB_OBJECT(ARRAY['url'], ARRAY[url]) as properties"),
            ])
            ->when($request->filled('start_at'), function ($query) use ($request) {
                return $query->where('timestamp', '>=', $request->date('start_at'));
            })->when($request->filled('end_at'), function ($query) use ($request) {
                return $query->where('timestamp', '<', $request->date('end_at'));
            })->when($request->filled('model_class'), function ($query) use ($request) {
                return $query->where('model_class', $request->string('model_class'));
            })->when($request->filled('object_uid'), function ($query) use ($request) {
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
                DB::raw("JSONB_OBJECT(
                    ARRAY['city', 'country', 'coordinates'],
                    ARRAY[{$prefix}location_data.city, {$prefix}location_data.country, {$prefix}location_data.coordinates::text]
                ) as location"),
                'properties',
            ])
            ->when($request->filled('start_at'), function ($query) use ($request) {
                return $query->where('timestamp', '>=', $request->date('start_at'));
            })->when($request->filled('end_at'), function ($query) use ($request) {
                return $query->where('timestamp', '<', $request->date('end_at'));
            })->when($request->filled('model_class'), function ($query) use ($request) {
                return $query->where('model_class', $request->string('model_class'));
            })->when($request->filled('object_uid'), function ($query) use ($request) {
                return $query->where('object_uid', $request->string('object_uid'));
            });

        $unionQuery = $requestsQuery->union($eventsQuery);

        $activities = $unionQuery->orderBy('timestamp', 'desc');

        return new ActivityCollection($activities->paginate());

    }
}

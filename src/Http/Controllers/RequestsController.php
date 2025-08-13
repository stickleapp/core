<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Http\Controllers\Requests\RequestsIndexRequest;
// use StickleApp\Core\Http\Controllers\Resources\RequestCollection;
use StickleApp\Core\Models\Request;

class RequestsController
{
    public function index(RequestsIndexRequest $request): JsonResponse
    {

        $prefix = config('stickle.database.tablePrefix');

        // Build base query for Requests (union of requests and events)
        $builder = Request::with('locationData')
            // ->select([
            //     DB::raw("'page_view' as Request_type"),
            //     'model_class',
            //     'object_uid',
            //     'session_uid',
            //     'timestamp',
            //     "{$prefix}requests.ip_address",
            //     // DB::raw("JSONB_OBJECT(
            //     //     ARRAY['city', 'country', 'coordinates'],
            //     //     ARRAY[{$prefix}location_data.city, {$prefix}location_data.country, {$prefix}location_data.coordinates::text]
            //     // ) as location"),
            //     'properties',
            // ])
            ->when($request->filled('start_at'), function ($query) use ($request) {
                return $query->where('timestamp', '>=', $request->date('start_at'));
            })->when($request->filled('end_at'), function ($query) use ($request) {
                return $query->where('timestamp', '<', $request->date('end_at'));
            })->when($request->filled('model_class'), function ($query) use ($request) {
                return $query->where('model_class', $request->string('model_class'));
            })->when($request->filled('object_uid'), function ($query) use ($request) {
                return $query->where('object_uid', $request->string('object_uid'));
            })
            ->orderBy('timestamp', 'desc');

        // return response()->json(new RequestCollection($builder->paginate()));

        return response()->json($builder->paginate($request->integer('per_page', 250)));
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use StickleApp\Core\Http\Controllers\Requests\RequestsIndexRequest;
use StickleApp\Core\Http\Controllers\Resources\RequestResource;
use StickleApp\Core\Models\Request;

class RequestsController
{
    public function index(RequestsIndexRequest $request): JsonResponse
    {

        $builder = Request::with('locationData')
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

        $paginated = $builder->paginate($request->integer('per_page', 250));

        return response()->json([
            'data' => RequestResource::collection($paginated->items()),
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from' => $paginated->firstItem(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}

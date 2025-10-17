<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use StickleApp\Core\Http\Controllers\Requests\RequestsIndexRequest;
use StickleApp\Core\Http\Controllers\Resources\RequestResource;
use StickleApp\Core\Models\Request;

class RequestsController
{
    public function index(RequestsIndexRequest $requestsIndexRequest): JsonResponse
    {

        $builder = Request::with('locationData')
            ->when($requestsIndexRequest->filled('start_at'), fn($query) => $query->where('timestamp', '>=', $requestsIndexRequest->date('start_at')))->when($requestsIndexRequest->filled('end_at'), fn($query) => $query->where('timestamp', '<', $requestsIndexRequest->date('end_at')))->when($requestsIndexRequest->filled('model_class'), fn($query) => $query->where('model_class', $requestsIndexRequest->string('model_class')))->when($requestsIndexRequest->filled('object_uid'), fn($query) => $query->where('object_uid', $requestsIndexRequest->string('object_uid')))
            ->orderBy('timestamp', 'desc');

        $lengthAwarePaginator = $builder->paginate($requestsIndexRequest->integer('per_page', 250));

        return response()->json([
            'data' => RequestResource::collection($lengthAwarePaginator->items()),
            'links' => [
                'first' => $lengthAwarePaginator->url(1),
                'last' => $lengthAwarePaginator->url($lengthAwarePaginator->lastPage()),
                'prev' => $lengthAwarePaginator->previousPageUrl(),
                'next' => $lengthAwarePaginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $lengthAwarePaginator->currentPage(),
                'from' => $lengthAwarePaginator->firstItem(),
                'last_page' => $lengthAwarePaginator->lastPage(),
                'per_page' => $lengthAwarePaginator->perPage(),
                'to' => $lengthAwarePaginator->lastItem(),
                'total' => $lengthAwarePaginator->total(),
            ],
        ]);
    }
}

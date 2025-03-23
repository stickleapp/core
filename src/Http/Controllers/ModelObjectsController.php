<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use StickleApp\Core\Http\Controllers\Requests\ModelObjectsIndexRequest;

class ModelObjectsController
{
    public function index(ModelObjectsIndexRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $model = data_get($validated, 'model');

        $class = config('stickle.namespaces.models').'\\'.Str::ucfirst($model);

        $search = data_get($validated, 'search'); // Get search term if provided

        $builder = $class::query()->when($search, function ($q) use ($search) {
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'ILIKE', "%{$search}%");
            });
        });

        return response()->json($builder->paginate($request->integer('per_page', 15)));
    }
}

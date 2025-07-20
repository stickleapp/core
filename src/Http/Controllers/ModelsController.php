<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use StickleApp\Core\Http\Controllers\Requests\ModelsIndexRequest;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

/**
 * Endpoint to  retrieve models that use the StickleEntity trait.
 */
class ModelsController
{
    public function index(ModelsIndexRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $modelClass = data_get($validated, 'model_class');

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst((string) $modelClass);

        if (! class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, StickleEntity::class)) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        $model = $modelClass::query()->getModel();

        $search = data_get($validated, 'search');
        $uid = data_get($validated, 'uid');

        $builder = $modelClass::query()
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'ILIKE', "%{$search}%");
                });
            })->when($uid, function ($q) use ($uid, $model) {
                return $q->where($model->getKeyName(), $uid);
            });

        return response()->json($builder->paginate($request->integer('per_page', 25)));
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;

class ModelRelationshipStatisticsController
{
    public function index(Request $request): JsonResponse
    {
        $prefix = config('stickle.database.tablePrefix');

        $attribute = $request->string('attribute');

        $model = $request->string('model');

        $class = config('stickle.namespaces.models').'\\'.Str::ucfirst($model);

        if (! class_exists($class)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($class, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        $object = $class::findOrFail($request->string('uid'));

        $builder = $model->objectStatistics()->where('attribute', $request->string('attribute'));

        return response()->json($builder->paginate(30));
    }
}

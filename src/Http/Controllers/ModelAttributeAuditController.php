<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;

class ModelAttributeAuditController
{
    public function index(Request $request): JsonResponse
    {
        $prefix = config('stickle.database.tablePrefix');

        $attribute = $request->string('attribute');

        $modelClass = $request->string('model_class');

        $modelClass = Str::ucfirst((string) $modelClass);

        if (! class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        $model = $modelClass::findOrFail($request->string('uid'));

        $builder = $model->modelAttributeAudits()->where('attribute', $request->string('attribute'));

        return response()->json($builder->paginate(30));
    }
}

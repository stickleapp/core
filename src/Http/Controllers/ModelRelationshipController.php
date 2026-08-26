<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Support\ClassUtils;

class ModelRelationshipController
{
    public function index(Request $request): JsonResponse
    {

        $modelClass = ClassUtils::tryResolveModelClass($request->string('model_class')->toString());

        if ($modelClass === null) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $model = $modelClass::findOrFail($request->string('object_uid')->toString());

        $relationship = $request->string('relationship')->toString();

        if (! method_exists($model, $relationship)) {
            return response()->json(['error' => 'Relationship not found'], 404);
        }

        $relatedModels = $model->$relationship()->paginate($request->integer('per_page', 25));

        return response()->json($relatedModels);
    }
}

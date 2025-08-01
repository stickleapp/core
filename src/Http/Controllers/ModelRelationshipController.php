<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelRelationshipController
{
    public function index(Request $request): JsonResponse
    {

        $modelClass = config('stickle.namespaces.models').'\\'.
            $request->string('model_class')->toString();

        if (! class_exists($modelClass)) {
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

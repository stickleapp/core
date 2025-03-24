<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use StickleApp\Core\Http\Controllers\Requests\ModelObjectsIndexRequest;

class ModelObjectAttributesController
{
    public function index(ModelObjectsIndexRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $model = data_get($validated, 'model');

        $class = config('stickle.namespaces.models').'\\'.Str::ucfirst($model);

        if (! class_exists($class)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        // Check if the class uses the StickleEntity trait
        $reflection = new \ReflectionClass($class);
        $traits = [];

        // Get all traits including those from parent classes
        $currentClass = $reflection;
        while ($currentClass) {
            $traitNames = array_keys($currentClass->getTraits());
            $traits = array_merge($traits, $traitNames);
            $currentClass = $currentClass->getParentClass();
        }

        $stickleEntityTrait = 'StickleApp\\Core\\Traits\\StickleEntity';
        if (! in_array($stickleEntityTrait, $traits)) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        // // Get the observed attributes
        // $observedAttributes = $class::getObservedAttributes();

        return response()->json();
    }
}

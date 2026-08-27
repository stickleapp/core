<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

/**
 * Endpoint to retrieve aggregate statistics for a group of models. Attribute
 * must be included in the 'stickleTrackedAttributes' array in the model.
 */
class ModelsStatisticsController
{
    public function index(Request $request): JsonResponse
    {
        $prefix = config('stickle.database.tablePrefix');

        $stringable = $request->string('attribute');

        $modelClass = $request->string('model_class');

        $modelClass = ClassUtils::tryResolveModelClass((string) $modelClass);

        if ($modelClass === null) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, StickleEntity::class)) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        // Get the model instance
        $model = $modelClass::query()->getModel();

        $builder = $modelClass::query()
            ->join("{$prefix}model_attributes", function ($join) use ($prefix, $model): void {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
                $join->where("{$prefix}model_attributes.model_class", '=', ClassUtils::storeModelClass($model));
            })
            ->selectRaw(
                "AVG(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_avg",
                [$stringable, $stringable]
            )
            ->selectRaw(
                "MIN(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_min",
                [$stringable, $stringable]
            )
            ->selectRaw(
                "MAX(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_max",
                [$stringable, $stringable]
            )
            ->selectRaw(
                "SUM(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_sum",
                [$stringable, $stringable]
            )
            ->selectRaw(
                'COUNT(*) as value_count'
            );

        return response()->json($builder->get());
    }
}

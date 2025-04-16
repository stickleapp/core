<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;

/**
 * Endpoint to retrieve aggregate statistics for a group of models. Attribute
 * must be included in the 'stickleTrackedAttributes' array in the model.
 */
class ModelsStatisticsController
{
    public function index(Request $request): JsonResponse
    {
        $prefix = config('stickle.database.tablePrefix');

        $attribute = $request->string('attribute');

        $modelClass = $request->string('model_class');

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst((string) $modelClass);

        if (! class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            return response()->json(['error' => 'Model does not use StickleEntity trait'], 400);
        }

        // Get the model instance
        $model = $modelClass::query()->getModel();

        $builder = $modelClass::query()
            ->join("{$prefix}model_attributes", function ($join) use ($prefix, $model) {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
                $join->where("{$prefix}model_attributes.model_class", '=', class_basename($model));
            })
            ->selectRaw(
                "AVG((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_avg",
                [$attribute]
            )
            ->selectRaw(
                "MIN((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_min",
                [$attribute]
            )
            ->selectRaw(
                "MAX((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_max",
                [$attribute]
            )
            ->selectRaw(
                "SUM((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_sum",
                [$attribute]
            )
            ->selectRaw(
                'COUNT(*) as value_count'
            );

        return response()->json($builder->get());
    }
}

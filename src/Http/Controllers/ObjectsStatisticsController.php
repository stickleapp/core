<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use StickleApp\Core\Support\ClassUtils;

class ObjectsStatisticsController
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

        // Get the model instance
        $model = $class::query()->getModel();

        $builder = $class::query()
            ->join("{$prefix}object_attributes", function ($join) use ($prefix, $model) {
                $join->on("{$prefix}object_attributes.object_uid", '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
                $join->where("{$prefix}object_attributes.model", '=', get_class($model));
            })
            ->selectRaw(
                "AVG((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_avg",
                [$attribute]
            )
            ->selectRaw(
                "MIN((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_min",
                [$attribute]
            )
            ->selectRaw(
                "MAX((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_max",
                [$attribute]
            )
            ->selectRaw(
                "SUM((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_sum",
                [$attribute]
            )
            ->selectRaw(
                'COUNT(*) as value_count'
            );

        return response()->json($builder->get());
    }
}

<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Models\Segment;

class SegmentsController
{
    public function index(Request $request): JsonResponse
    {
        $builder = Segment::query()
            ->when($request->input('model_class'), function ($query) use ($request) {
                $query->where('model_class', $request->input('model_class'));
            });

        // Get the paginated results
        $segments = $builder->paginate($request->integer('per_page', 15));

        // Add metadata to each segment
        $segments->through(function ($segment) {

            // Retrieve metadata.. vs

            // TODO... work here.. on AttributeUtils (All for the class? Just TARGET_CLASS?)
            if (! $metadata = \StickleApp\Core\Support\AttributeUtils::getAttributeForClass(
                config('stickle.namespaces.segments').'\\'.$segment->as_class,
                StickleSegmentMetadata::class
            )) {
                return $segment;
            }

            // Append the required fields
            $segment->name = data_get($metadata, 'name');
            $segment->description = data_get($metadata, 'description');
            $segment->refresh_interval = data_get($metadata, 'refreshInterval');

            return $segment;
        });

        return response()->json($segments);
    }
}

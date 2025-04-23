<?php

declare(strict_types=1);

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Models\Segment;

class SegmentModelsController
{
    public function index(Request $request): JsonResponse
    {
        $segmentId = $request->integer('segment_id');

        $segment = Segment::findOrFail($segmentId);

        return response()->json($segment->objects()->paginate($request->integer('per_page', 15)));
    }
}

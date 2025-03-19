<?php

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Models\SegmentModel;

class SegmentObjectsController
{
    public function index(Request $request): JsonResponse
    {
        $segmentId = $request->integer('segment_id');

        $segment = SegmentModel::findOrFail($segmentId);

        return response()->json($segment->objects()->get());
    }
}

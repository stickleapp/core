<?php

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Models\SegmentModel;

class SegmentsController
{
    public function index(Request $request): JsonResponse
    {
        $segments = SegmentModel::all();

        return response()->json($segments);
    }
}

<?php

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StickleApp\Core\Models\Segment;

class SegmentsController
{
    public function index(Request $request): JsonResponse
    {
        $segments = Segment::all();

        return response()->json($segments);
    }
}

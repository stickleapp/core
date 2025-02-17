<?php

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\Request;
use StickleApp\Core\Models\Segment;

class SegmentObjectsController
{
    public function index(Request $request)
    {
        $segmentId = $request->integer('segment_id');

        $segment = Segment::findOrFail($segmentId);

        return response()->json($segment->objects()->get());
    }
}

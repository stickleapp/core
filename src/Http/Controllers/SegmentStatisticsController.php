<?php

namespace StickleApp\Core\Http\Controllers;

use Illuminate\Http\Request;
use StickleApp\Core\Models\SegmentStatistic;

class SegmentStatisticsController
{
    public function index(Request $request)
    {
        $attribute = $request->string('attribute');

        $segmentId = $request->integer('segment_id');

        $dateFrom = $request->date('date_from');

        $dateTo = $request->date('date_to');

        $segmentStatistics = SegmentStatistic::where('segment_id', $segmentId)
            ->where('attribute', $attribute)
            ->when($dateFrom, function ($query) use ($dateFrom) {
                return $query->where('recorded_at', '>=', $dateFrom->startOfDay());
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                return $query->where('recorded_at', '<=', $dateTo->endOfDay());
            })
            ->get();

        return response()->json($segmentStatistics);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;
use StickleApp\Core\Http\Controllers\SegmentStatisticsController;

/**
 * API Routes
 */
Route::middleware(['api'])->group(function () {

    Route::post('/stickle/api/track', [IngestController::class, 'store'])
        ->name('stickle/track');

    Route::get('/stickle/api/segment-statistics', [SegmentStatisticsController::class, 'index'])
        ->name('segment-statistics');
});

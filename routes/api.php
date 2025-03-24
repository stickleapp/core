<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;
use StickleApp\Core\Http\Controllers\ModelObjectsController;
use StickleApp\Core\Http\Controllers\SegmentObjectsController;
use StickleApp\Core\Http\Controllers\SegmentStatisticsController;

/**
 * API Routes
 */
Route::middleware(['api'])->group(function () {

    Route::post('/stickle/api/track', [IngestController::class, 'store'])
        ->name('stickle/track');

    Route::get('/stickle/api/segment-statistics', [SegmentStatisticsController::class, 'index'])
        ->name('segment-statistics');

    Route::get('/stickle/api/segment-objects', [SegmentObjectsController::class, 'index'])
        ->name('segment-objects');

    Route::get('/stickle/api/model-objects', [ModelObjectsController::class, 'index'])
        ->name('model-objects');
});

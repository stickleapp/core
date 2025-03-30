<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;
use StickleApp\Core\Http\Controllers\ObjectsController;
use StickleApp\Core\Http\Controllers\ObjectsStatisticsController;
use StickleApp\Core\Http\Controllers\ObjectStatisticsController;
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

    Route::get('/stickle/api/objects', [ObjectsController::class, 'index'])
        ->name('objects');

    Route::get('/stickle/api/objects-statistics', [ObjectsStatisticsController::class, 'index'])
        ->name('objects-statistics');

    Route::get('/stickle/api/object-statistics', [ObjectStatisticsController::class, 'index'])
        ->name('object-statistics');

    Route::get('/stickle/api/object-attributes', [ObjectAttributesController::class, 'index'])
        ->name('object-attributes');
});

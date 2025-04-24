<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;
use StickleApp\Core\Http\Controllers\ModelAttributeAuditController;
use StickleApp\Core\Http\Controllers\ModelRelationshipController;
use StickleApp\Core\Http\Controllers\ModelRelationshipStatisticsController;
use StickleApp\Core\Http\Controllers\ModelsController;
use StickleApp\Core\Http\Controllers\ModelsStatisticsController;
use StickleApp\Core\Http\Controllers\SegmentModelsController;
use StickleApp\Core\Http\Controllers\SegmentsController;
use StickleApp\Core\Http\Controllers\SegmentStatisticsController;

/**
 * API Routes
 */
Route::middleware(['api'])->group(function () {

    Route::post('/stickle/api/track', [IngestController::class, 'store'])
        ->name('stickle/track');

    Route::get('/stickle/api/segment-statistics', [SegmentStatisticsController::class, 'index'])
        ->name('segment-statistics');

    Route::get('/stickle/api/segment-models', [SegmentModelsController::class, 'index'])
        ->name('segment-models');

    Route::get('/stickle/api/segments', [SegmentsController::class, 'index'])
        ->name('segments');

    Route::get('/stickle/api/models', [ModelsController::class, 'index'])
        ->name('models');

    Route::get('/stickle/api/models-statistics', [ModelsStatisticsController::class, 'index'])
        ->name('models-statistics');

    Route::get('/stickle/api/model-relationship', [ModelRelationshipController::class, 'index'])
        ->name('models-relationship');

    Route::get('/stickle/api/model-relationship-statistics', [ModelRelationshipStatisticsController::class, 'index'])
        ->name('model-relationship-statistics');

    Route::get('/stickle/api/model-attribute-audit', [ModelAttributeAuditController::class, 'index'])
        ->name('model-attribute-audit');
});

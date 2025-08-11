<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\ActivitiesController;
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
Route::middleware(config('stickle.routes.api.middleware', ['api']))
    ->prefix(config('stickle.routes.api.prefix', 'stickle/api'))->group(function () {

        Route::post('/track', [IngestController::class, 'store'])
            ->name('stickle/track');

        Route::get('/activities', [ActivitiesController::class, 'index'])
            ->name('stickle::api.activities');

        Route::get('/segment-statistics', [SegmentStatisticsController::class, 'index'])
            ->name('segment-statistics');

        Route::get('/segment-models', [SegmentModelsController::class, 'index'])
            ->name('segment-models');

        Route::get('/segments', [SegmentsController::class, 'index'])
            ->name('segments');

        Route::get('/models', [ModelsController::class, 'index'])
            ->name('models');

        Route::get('/models-statistics', [ModelsStatisticsController::class, 'index'])
            ->name('models-statistics');

        Route::get('/model-relationship', [ModelRelationshipController::class, 'index'])
            ->name('models-relationship');

        Route::get('/model-relationship-statistics', [ModelRelationshipStatisticsController::class, 'index'])
            ->name('model-relationship-statistics');

        Route::get('/model-attribute-audit', [ModelAttributeAuditController::class, 'index'])
            ->name('model-attribute-audit');
    });

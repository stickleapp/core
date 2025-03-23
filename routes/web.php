<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Routes for the demo
 */
Route::middleware(['web'])->group(function () {

    /** Stickle UI */
    Route::view('/stickle', 'stickle::pages/live')
        ->name('stickle::index');
    Route::view('/stickle/live', 'stickle::pages/live')
        ->name('stickle::live');
    Route::view('/stickle/{modelName}/segments/{segmentId}', 'stickle::pages/segment')
        ->name('stickle::segments')
        ->where('modelName', '[^/]+');
    Route::view('/stickle/{modelName}/segments', 'stickle::pages/segments')
        ->name('stickle::segments')
        ->where('modelName', '[^/]+');
    Route::view('/stickle/{modelName}/{modelUid}', 'stickle::pages/model')
        ->name('stickle::model')
        ->where('modelName', '[^/]+');
    Route::get('/stickle/{model}', function (Request $request) {
        return view('stickle::pages/models', [
            'model' => $request->route('model'),
        ]);
    })
        ->name('stickle::models')
        ->where('model', '[^/]+');

    /** Installation Demo */
    Route::view('/stickle-demo', 'stickle::demo/index')
        ->name('stickle::demo/index');

    Route::view('/stickle-app', 'stickle::demo/app')
        ->name('stickle::demo/app');

    Route::view('/stickle-integration', 'stickle::demo/integration')
        ->name('stickle::demo/integration');

    Route::view('/stickle-admin', 'stickle::demo/admin')
        ->name('stickle::demo/admin');
});

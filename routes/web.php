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

    Route::get(
        '/stickle/{class}/{uid}/{relatedClass}',
        function (Request $request) {
            $class = config('stickle.namespaces.models').'\\'.ucfirst($request->route('class'));
            $model = $class::findOrFail($request->route('uid'));

            return view('stickle::pages/model-relationship', [
                'class' => $request->route('class'),
                'uid' => $request->route('uid'),
                'model' => $model,
                'relationship' => $request->route('relatedClass'),
            ]);
        })
        ->name('stickle::model.relationship')
        ->where('class', '[^/]+')
        ->where('uid', '[^/]+')
        ->where('relatedClass', '[^/]+');

    Route::get('/stickle/{class}/{uid}', function (Request $request) {
        $class = config('stickle.namespaces.models').'\\'.ucfirst($request->route('class'));
        $model = $class::findOrFail($request->route('uid'));

        return view('stickle::pages/model', [
            'class' => $request->route('class'),
            'uid' => $request->route('uid'),
            'model' => $model,
        ]);
    })
        ->name('stickle::model')
        ->where('class', '[^/]+')
        ->where('uid', '[^/]+');
    Route::get('/stickle/{class}', function (Request $request) {
        return view('stickle::pages/models', [
            'class' => $request->route('class'),
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

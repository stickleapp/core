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
    Route::view('/stickle/{modelClass}/segments/{segmentId}', 'stickle::pages/segment')
        ->name('stickle::segments')
        ->where('modelClass', '[^/]+');
    Route::view('/stickle/{modelClass}/segments', 'stickle::pages/segments')
        ->name('stickle::segments')
        ->where('modelClass', '[^/]+');

    Route::get(
        '/stickle/{modelClass}/{uid}/{relatedClass}',
        function (Request $request) {
            $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));
            $model = $modelClass::findOrFail($request->route('uid'));

            return view('stickle::pages/model-relationship', [
                'modelClass' => $request->route('modelClass'),
                'uid' => $request->route('uid'),
                'model' => $model,
                'relationship' => $request->route('relatedClass'),
            ]);
        })
        ->name('stickle::model.relationship')
        ->where('modelClass', '[^/]+')
        ->where('uid', '[^/]+')
        ->where('relatedClass', '[^/]+');

    Route::get('/stickle/{modelClass}/{uid}', function (Request $request) {
        $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));
        $model = $modelClass::findOrFail($request->route('uid'));

        return view('stickle::pages/model', [
            'modelClass' => $request->route('modelClass'),
            'uid' => $request->route('uid'),
            'model' => $model,
        ]);
    })
        ->name('stickle::model')
        ->where('modelClass', '[^/]+')
        ->where('uid', '[^/]+');

    Route::get('/stickle/{modelClass}', function (Request $request) {
        return view('stickle::pages/models', [
            'modelClass' => $request->route('modelClass'),
        ]);
    })
        ->name('stickle::models')
        ->where('modelClass', '[^/]+');

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

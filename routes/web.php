<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use StickleApp\Core\Models\Segment;

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

    Route::get('/stickle/{modelClass}/segments', function (Request $request) {
        $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));

        return view('stickle::pages/segments', [
            'modelClass' => $request->route('modelClass'),
        ]);
    })
        ->name('stickle::segments')
        ->where('modelClass', '[^/]+');

    Route::get('/stickle/{modelClass}/segments/{segmentId}', function (Request $request) {
        $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));
        $segment = Segment::findOrFail($request->route('segmentId'));

        return view('stickle::pages/segment', [
            'modelClass' => $request->route('modelClass'),
            'segment' => $segment,
        ]);
    })
        ->name('stickle::segment')
        ->where('modelClass', '[^/]+')
        ->where('segmentId', '[^/]+');

    Route::get(
        '/stickle/{modelClass}/{uid}/{relationship}',
        function (Request $request) {
            $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));
            $model = $modelClass::findOrFail($request->route('uid'));

            return view('stickle::pages/model-relationship', [
                'modelClass' => $request->route('modelClass'),
                'uid' => $request->route('uid'),
                'model' => $model,
                'relationship' => $request->route('relationship'),
            ]);
        })
        ->name('stickle::model.relationship')
        ->where('modelClass', '[^/]+')
        ->where('uid', '[^/]+')
        ->where('relationship', '[^/]+');

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

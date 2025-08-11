<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use StickleApp\Core\Models\Segment;

/**
 * Routes for the demo
 */
Route::middleware(config('stickle.routes.web.middleware', []))
    ->prefix(config('stickle.routes.web.prefix', 'stickle'))->group(function () {

        /** Stickle UI */
        Route::get('/live', function (Request $request) {

            $modelClass = config('stickle.namespaces.models').'\\User';

            if ($request->filled(['model_class', 'uid'])) {
                $eventsChannel = sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', strtolower(class_basename($modelClass))),
                    $request->string('uid')
                );
                $model = $modelClass::findOrFail($request->route('uid'));
            } else {
                $eventsChannel = config('stickle.broadcasting.channels.firehose');
            }

            return view('stickle::pages/live', [
                'modelClass' => $modelClass,
                'uid' => $request->string('uid'),
                'model' => $model ?? null,
                'eventsChannel' => $eventsChannel,
                'location' => $request->string('location'),
            ]);
        })->name('stickle::live');
        Route::redirect('', config('stickle.routes.web.prefix').'/live');

        Route::view('/{modelClass}/segments/{segmentId}', 'stickle::pages/segment')
            ->name('stickle::segments')
            ->where('modelClass', '[^/]+');
        Route::view('/{modelClass}/segments', 'stickle::pages/segments')
            ->name('stickle::segments')
            ->where('modelClass', '[^/]+');

        Route::get('/{modelClass}/segments', function (Request $request) {
            $modelClass = config('stickle.namespaces.models').'\\'.ucfirst($request->route('modelClass'));

            return view('stickle::pages/segments', [
                'modelClass' => $request->route('modelClass'),
            ]);
        })
            ->name('stickle::segments')
            ->where('modelClass', '[^/]+');

        Route::get('/{modelClass}/segments/{segmentId}', function (Request $request) {
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
            '/{modelClass}/{uid}/{relationship}',
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

        Route::get('/{modelClass}/{uid}', function (Request $request) {
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

        Route::get('/{modelClass}', function (Request $request) {
            return view('stickle::pages/models', [
                'modelClass' => $request->route('modelClass'),
            ]);
        })
            ->name('stickle::models')
            ->where('modelClass', '[^/]+');

        /** Installation Demo */
        Route::view('/demo', 'stickle::demo/index')
            ->name('stickle::demo/index');

        Route::view('/app', 'stickle::demo/app')
            ->name('stickle::demo/app');

        Route::view('/integration', 'stickle::demo/integration')
            ->name('stickle::demo/integration');

        Route::view('/admin', 'stickle::demo/admin')
            ->name('stickle::demo/admin');
    });

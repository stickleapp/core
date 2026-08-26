<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

/**
 * The model segment matches only models that actually use StickleEntity, so an
 * unknown name 404s here rather than reaching a view that tries to load it and
 * throws. '[^/]+' let /stickle/segments -- a reasonable guess -- resolve as a
 * model named "Segments" and return a 500.
 *
 * Resolved once per request rather than per route, and falls back to the old
 * catch-all if the namespace cannot be scanned, so a misconfigured
 * namespaces.models makes the pages 404 rather than vanish.
 */
$trackedModels = array_map(
    ClassUtils::storeModelClass(...),
    ClassUtils::getClassesWithTrait(
        config('stickle.namespaces.models'),
        StickleEntity::class
    )
);

$modelPattern = $trackedModels === []
    ? '[^/]+'
    : implode('|', array_map(preg_quote(...), $trackedModels));

/**
 * Routes for the demo
 */
/**
 * can:viewStickle is written here rather than read from config, so no
 * configuration value can remove it. The configured list is transport
 * plumbing only -- session, cookies, and optionally auth for a login
 * redirect instead of a bare 403.
 */
Route::middleware([
    ...Arr::wrap(config('stickle.routes.web.middleware', ['web'])),
    'can:viewStickle',
])
    ->prefix(config('stickle.routes.web.prefix', 'stickle'))
    ->group(function () use ($modelPattern): void {
        /** Stickle UI */
        Route::get('/live', function (Request $request): Factory|View {
            $modelClass = config('stickle.namespaces.models').'\\User';

            if ($request->filled(['model_class', 'uid'])) {
                $eventsChannel = sprintf(
                    config('stickle.broadcasting.channels.object'),
                    str_replace(
                        '\\',
                        '-',
                        strtolower(class_basename($modelClass)),
                    ),
                    $request->string('uid'),
                );
                /** Scoped to match the channel, for the polling fallback */
                $eventsEndpoint = route('stickle::api.requests', [
                    'model_class' => class_basename($modelClass),
                    'object_uid' => $request->string('uid'),
                ]);
                $model = $modelClass::findOrFail($request->route('uid'));
            } else {
                $eventsChannel = config(
                    'stickle.broadcasting.channels.firehose',
                );
                $eventsEndpoint = route('stickle::api.requests');
            }

            return view('stickle::pages/live', [
                'modelClass' => $modelClass,
                'uid' => $request->string('uid'),
                'model' => $model ?? null,
                'eventsChannel' => $eventsChannel,
                'eventsEndpoint' => $eventsEndpoint,
                'location' => $request->string('location'),
            ]);
        })->name('stickle::live');
        Route::redirect('', config('stickle.routes.web.prefix').'/live');

        Route::view(
            '/{modelClass}/segments/{segmentId}',
            'stickle::pages/segment',
        )
            ->name('stickle::segments')
            ->where('modelClass', $modelPattern);
        Route::view('/{modelClass}/segments', 'stickle::pages/segments')
            ->name('stickle::segments')
            ->where('modelClass', $modelPattern);

        Route::get('/{modelClass}/segments', function (
            Request $request,
        ): Factory|View {
            $modelClass =
                config('stickle.namespaces.models').
                '\\'.
                ucfirst($request->route('modelClass'));

            return view('stickle::pages/segments', [
                'modelClass' => $request->route('modelClass'),
            ]);
        })
            ->name('stickle::segments')
            ->where('modelClass', $modelPattern);

        Route::get('/{modelClass}/segments/{segmentId}', function (
            Request $request,
        ): Factory|View {
            $modelClass =
                config('stickle.namespaces.models').
                '\\'.
                ucfirst($request->route('modelClass'));
            $segment = Segment::query()->findOrFail(
                $request->route('segmentId'),
            );

            return view('stickle::pages/segment', [
                'modelClass' => $request->route('modelClass'),
                'segment' => $segment,
            ]);
        })
            ->name('stickle::segment')
            ->where('modelClass', $modelPattern)
            ->where('segmentId', '[^/]+');

        Route::get('/{modelClass}/{uid}/{relationship}', function (
            Request $request,
        ): Factory|View {
            $modelClass =
                config('stickle.namespaces.models').
                '\\'.
                ucfirst($request->route('modelClass'));
            $model = $modelClass::findOrFail($request->route('uid'));

            return view('stickle::pages/model-relationship', [
                'modelClass' => $request->route('modelClass'),
                'uid' => $request->route('uid'),
                'model' => $model,
                'relationship' => $request->route('relationship'),
            ]);
        })
            ->name('stickle::model.relationship')
            ->where('modelClass', $modelPattern)
            ->where('uid', '[^/]+')
            ->where('relationship', '[^/]+');

        Route::get('/{modelClass}/{uid}', function (
            Request $request,
        ): Factory|View {
            $modelClass =
                config('stickle.namespaces.models').
                '\\'.
                ucfirst($request->route('modelClass'));
            $model = $modelClass::findOrFail($request->route('uid'));

            return view('stickle::pages/model', [
                'modelClass' => $request->route('modelClass'),
                'uid' => $request->route('uid'),
                'model' => $model,
            ]);
        })
            ->name('stickle::model')
            ->where('modelClass', $modelPattern)
            ->where('uid', '[^/]+');

        Route::get(
            '/{modelClass}',
            fn (Request $request): Factory|View => view(
                'stickle::pages/models',
                [
                    'modelClass' => $request->route('modelClass'),
                ],
            ),
        )
            ->name('stickle::models')
            ->where('modelClass', $modelPattern);

        /** Installation Demo */
        Route::view('/demo', 'stickle::demo/index')->name(
            'stickle::demo/index',
        );

        Route::view('/app', 'stickle::demo/app')->name('stickle::demo/app');

        Route::view('/integration', 'stickle::demo/integration')->name(
            'stickle::demo/integration',
        );

        Route::view('/admin', 'stickle::demo/admin')->name(
            'stickle::demo/admin',
        );
    });

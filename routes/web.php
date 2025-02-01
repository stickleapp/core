<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes for the demo
 */
Route::middleware(['web'])->group(function () {

    /** Installation Demo */
    Route::view('/stickle', 'stickle::components/ui/index')
        ->name('stickle::ui/index');

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

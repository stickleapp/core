<?php

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
    Route::view('/stickle/users', 'stickle::pages/users')
        ->name('stickle::users');
    Route::view('/stickle/users/{id}', 'stickle::pages/user')
        ->name('stickle::user');

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

<?php

use App\Models\User;
use Dclaysmith\LaravelCascade\Http\Controllers\IngestController;
use Illuminate\Support\Facades\Route;

/**
 * Routes for the demo
 */
Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/cascade', function () {
        return view('cascade::demo/index');
    })->name('cascade::demo/index');

    Route::view('/cascade-app', 'cascade::demo/app')
        ->name('cascade::demo/app');

    Route::view('/cascade-integration', 'cascade::demo/integration')
        ->name('cascade::demo/integration');

    Route::get('/cascade-admin', function () {
        $users = []; // User::all();

        return view('cascade::demo/admin', ['users' => $users]);
    })->name('cascade::demo/admin');

    Route::post('/cascade-track', [IngestController::class, 'store'])
        ->name('cascade/track');
});

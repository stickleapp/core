<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;

/**
 * Routes for the demo
 */
Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/stickle', function () {
        return view('stickle::demo/index');
    })->name('stickle::demo/index');

    Route::view('/stickle-app', 'stickle::demo/app')
        ->name('stickle::demo/app');

    Route::view('/stickle-integration', 'stickle::demo/integration')
        ->name('stickle::demo/integration');

    Route::get('/stickle-admin', function () {
        $users = []; // User::all();

        return view('stickle::demo/admin', ['users' => $users]);
    })->name('stickle::demo/admin');

    Route::post('/stickle-track', [IngestController::class, 'store'])
        ->name('stickle/track');
});

<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;

/**
 * Routes for the demo
 */
Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/STICKLE', function () {
        return view('STICKLE::demo/index');
    })->name('STICKLE::demo/index');

    Route::view('/STICKLE-app', 'STICKLE::demo/app')
        ->name('STICKLE::demo/app');

    Route::view('/STICKLE-integration', 'STICKLE::demo/integration')
        ->name('STICKLE::demo/integration');

    Route::get('/STICKLE-admin', function () {
        $users = []; // User::all();

        return view('STICKLE::demo/admin', ['users' => $users]);
    })->name('STICKLE::demo/admin');

    Route::post('/STICKLE-track', [IngestController::class, 'store'])
        ->name('STICKLE/track');
});

<?php

use Illuminate\Support\Facades\Route;
use StickleApp\Core\Http\Controllers\IngestController;

/**
 * API Routes
 */
Route::middleware(['api'])->group(function () {

    Route::post('/stickle/track', [IngestController::class, 'store'])
        ->name('stickle/track');
});

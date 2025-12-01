<?php
// routes/api.php

use App\Http\Controllers\HighFlyerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('high-flyer')->group(function () {
        Route::post('/start', [HighFlyerController::class, 'startGame']);
        Route::post('/cashout', [HighFlyerController::class, 'cashOut']);
        Route::post('/crash', [HighFlyerController::class, 'crash']);
        Route::get('/history', [HighFlyerController::class, 'getUserHistory']);
    });
});
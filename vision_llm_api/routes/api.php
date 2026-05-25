<?php

use App\Http\Controllers\ExtractionController;
use Illuminate\Support\Facades\Route;

Route::prefix('extract')->group(function () {
    Route::post('/carte-grise', [ExtractionController::class, 'carteGrise']);
    Route::post('/carte-verte', [ExtractionController::class, 'carteVerte']);
    Route::post('/permis',      [ExtractionController::class, 'permis']);
    Route::post('/constat',     [ExtractionController::class, 'constat']);
    Route::post('/auto',        [ExtractionController::class, 'auto']);
});
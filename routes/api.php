<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaraniController;
use App\Http\Controllers\KotaController;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('can:isAdmin')->group(function () {
        Route::apiResource('/karani', KaraniController::class);
        Route::apiResource('/kota', KotaController::class);
    });
});
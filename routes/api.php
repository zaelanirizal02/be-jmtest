<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route Pasien
    Route::get('/pasien', [PatientController::class, 'index']);
    Route::post('/pasien', [PatientController::class, 'store']);
    Route::get('/pasien/{id}', [PatientController::class, 'show']);
    Route::put('/pasien/{id}', [PatientController::class, 'update']);
    Route::delete('/pasien/{id}', [PatientController::class, 'destroy']);
});


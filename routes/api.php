<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientHealthController;
use App\Http\Controllers\DiagnosisController;
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

    // Route Data Kesehatan Pasien
    Route::get('/pasien/{patientId}/kesehatan', [PatientHealthController::class, 'index']);
    Route::post('/pasien/{patientId}/kesehatan', [PatientHealthController::class, 'store']);
    Route::get('/pasien/{patientId}/kesehatan/{id}', [PatientHealthController::class, 'show']);
    Route::put('/pasien/{patientId}/kesehatan/{id}', [PatientHealthController::class, 'update']);
    Route::delete('/pasien/{patientId}/kesehatan/{id}', [PatientHealthController::class, 'destroy']);

    // Route Diagnosis Pasien
    Route::get('/pasien/{patientId}/diagnosis', [DiagnosisController::class, 'index']);
    Route::post('/pasien/{patientId}/diagnosis', [DiagnosisController::class, 'store']);
    Route::get('/pasien/{patientId}/diagnosis/{id}', [DiagnosisController::class, 'show']);
    Route::put('/pasien/{patientId}/diagnosis/{id}', [DiagnosisController::class, 'update']);
    Route::delete('/pasien/{patientId}/diagnosis/{id}', [DiagnosisController::class, 'destroy']);
});


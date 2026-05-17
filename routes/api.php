<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\VersionController;

// Auth
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/documents', [DocumentController::class, 'index']);
});

// ============================================================
// Endpoint Versi Dokumen (dipanggil dari Vue frontend via axios)
// PENTING: route spesifik /versions/data/{id} harus di ATAS
// route dengan wildcard /versions/{documentName}
// ============================================================
Route::get('/versions/data/{id}', [VersionController::class, 'showData']);
Route::get('/versions/{documentName}', [VersionController::class, 'index']);
Route::post('/versions/{documentName}', [VersionController::class, 'store']);

// ============================================================
// Endpoint diakses Node.js Socket Server (tanpa Sanctum token)
// PENTING: harus di BAWAH semua route /versions agar tidak konflik
// ============================================================
Route::get('/documents/{name}', [DocumentController::class, 'show']);
Route::put('/documents/{name}', [DocumentController::class, 'update']);

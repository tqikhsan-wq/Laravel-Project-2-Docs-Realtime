<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\VersionController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{name}', [DocumentController::class, 'show']);
    Route::put('/documents/{name}', [DocumentController::class, 'update']);
    
    Route::get('/documents/{name}/versions', [VersionController::class, 'index']);
    Route::post('/documents/{name}/versions', [VersionController::class, 'store']);
});

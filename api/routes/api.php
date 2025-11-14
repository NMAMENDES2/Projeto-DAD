<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request; 

// ===========================
// ROTAS PÚBLICAS
// ===========================

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/photos/{filename}', [UserController::class, 'showPhoto']);

Route::get('/users/{id}', [UserController::class, 'teste']);

// ===========================
// ROTAS PROTEGIDAS
// ===========================

Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticação
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    
    // Ver perfil próprio
    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });
    
    // Upload foto e password
    Route::post('/users/{userId}/photo', [UserController::class, 'uploadPhoto']);
    Route::post('/users/{userId}/password', [UserController::class, 'updatePassword']);
    Route::post('/users/{userId}/block', [UserController::class, 'toggleBlock']);
    
    // CRUD de utilizadores (estilo professor)
    Route::apiResource('users', UserController::class);
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// ===========================
// ROTAS PÚBLICAS
// ===========================

// Autenticação (G1)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Imagens públicas
Route::get('/image/{filename}', [UserController::class, 'image']);

// ===========================
// ROTAS PROTEGIDAS (auth:sanctum)
// ===========================

Route::middleware('auth:sanctum')->group(function () {
    
    // ----------------------
    // AUTENTICAÇÃO E PERFIL (G1)
    // ----------------------
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::get('/users/me', [UserController::class, 'showMe']);
    Route::put('/profile', [UserController::class, 'edit']);
    Route::post('/profile/image/{userId}', [UserController::class, 'updatePicture']);
    Route::post('/change-password/{userId}', [UserController::class, 'changePassword']);
    Route::delete('/user-delete', [UserController::class, 'destroy']);

    // ----------------------
    // ADMINISTRAÇÃO (G5)
    // ✅ Verificação feita dentro dos controllers
    // ----------------------
    Route::post('/admins', [UserController::class, 'createAdmin']);
    Route::get('/users', [UserController::class, 'index']);
    Route::delete('/users/{id}', [UserController::class, 'userDestroy']);
    Route::post('/users/{userId}/block', [UserController::class, 'block']);
});

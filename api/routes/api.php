<?php

use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controllers existentes
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CoinController;

// Controllers do jogo (a criar)
use App\Http\Controllers\GameController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GameHistoryController;

// =====================================
// ROTAS PÚBLICAS
// =====================================

// Autenticação
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Fotos (perfil)
Route::get('/photos/{filename}', [UserController::class, 'showPhoto']);

// Leaderboards globais (quando existir GameController)
// Route::get('/games/globalscores/singleplayer/{type}', [GameController::class, 'globalScoresSingleplayer']);
// Route::get('/games/globalscores/multiplayer/{type}', [GameController::class, 'globalScoresMultiplayer']);

// Estatísticas globais (quando existir StatisticsController)
// Route::get('/statistics', [StatisticsController::class, 'getSummary']);


// =====================================
// ROTAS PROTEGIDAS (auth:sanctum)
// =====================================
Route::middleware('auth:sanctum')->group(function () {

    // ###############################
    // AUTH
    // ###############################
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);

    Route::get('/auth/me', function (Request $request) {
        return $request->user();
    });

    // ###############################
    // PERFIL
    // ###############################
    Route::get('/users/me', [UserController::class, 'me']);
    Route::put('/users/me', [UserController::class, 'update']);

    Route::post('/users/me/photo', [UserController::class, 'uploadPhoto']);
    Route::post('/users/me/password', [UserController::class, 'updatePassword']);

    // ###############################
    // SINGLEPLAYER (Bisca)
    // ###############################
    // Route::post('/games/singleplayer/start', [GameController::class, 'startSinglePlayer']);
    // Route::post('/games/singleplayer/play', [GameController::class, 'playCardSinglePlayer']);
    // Route::post('/games/singleplayer/finish', [GameController::class, 'finishSinglePlayer']);

    // ###############################
    // MULTIPLAYER (Bisca + WebSockets)
    // ###############################
    // Route::post('/games/multiplayer/create', [GameController::class, 'createMultiplayer']);
    // Route::post('/games/multiplayer/join/{gameId}', [GameController::class, 'joinMultiplayer']);
    // Route::post('/games/multiplayer/leave/{gameId}', [GameController::class, 'leaveMultiplayer']);
    // Route::post('/games/multiplayer/finish/{gameId}', [GameController::class, 'finishMultiplayer']);

    // ###############################
    // HISTÓRICO DE JOGOS
    // ###############################
    // Route::get('/game-history', [GameHistoryController::class, 'getPersonalGameHistory']);
    // Route::get('/games', [GameController::class, 'index']);

    // ###############################
    // ESTATÍSTICAS PESSOAIS
    // ###############################
    // Route::get('/personal-statistics', [StatisticsController::class, 'getPersonalStatistics']);
    // ###############################
    // TRANSAÇÕES / COINS
    // ###############################
    Route::get('coins/balance', [CoinController::class, 'getBalance']);
    Route::post('coins/purchase', [CoinController::class, 'purchaseCoins']);
    Route::get('coins/transactions', [CoinController::class, 'getTransactions']);

    // ADMIN – gestão de utilizadores
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/block', [UserController::class, 'toggleBlock']);

    // ADMIN – gestão de transações
    Route::get('admin/coins/transactions', [CoinController::class, 'getAllTransactions']);
    Route::get('admin/users/{id}/transactions', [CoinController::class, 'getUserTransactions']);
});


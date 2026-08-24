<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota de Login do Desktop (Pública)
Route::post('/v1/desktop/login', [\App\Http\Controllers\Api\DesktopAuthController::class, 'login']);

// Rotas protegidas por Token
Route::middleware('auth:sanctum')->group(function () {
    // Rota para o Desktop App enviar arquivos (Sincronização Contínua)
    Route::post('/v1/desktop/sync-file', [\App\Http\Controllers\Api\DesktopSyncController::class, 'syncFile']);
});

<?php

use App\Http\Controllers\Api\MetricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/metrics', [MetricController::class, 'store']);

// Ruta de ejemplo de Sanctum (la dejamos por si más adelante autenticamos usuarios por token)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

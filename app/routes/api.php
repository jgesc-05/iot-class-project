<?php

use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MetricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ingesta de metricas (dia 3)
Route::post('/metrics', [MetricController::class, 'store']);

// Lectura publica de la ultima metrica (dia 6)
Route::get('/devices/{device_id}/latest', [DeviceController::class, 'latest']);

// Comandos (dia 7)
Route::get('/devices/{device_id}/commands', [CommandController::class, 'pending']);
Route::patch('/devices/{device_id}/commands/{command_id}/ack', [CommandController::class, 'ack']);
Route::post('/commands', [CommandController::class, 'store']);

// Ruta de ejemplo de Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

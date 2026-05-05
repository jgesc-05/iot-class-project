<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MetricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ingesta de metricas (dia 3)
Route::post('/metrics', [MetricController::class, 'store']);

// Lectura publica del estado de un dispositivo
Route::get('/devices/{device_id}/latest', [DeviceController::class, 'latest']);
Route::get('/devices/{device_id}/metrics', [DeviceController::class, 'metrics']);
Route::get('/devices/{device_id}/stats', [DeviceController::class, 'stats']);
Route::get('/devices/{device_id}/history', [DeviceController::class, 'history']);

// Comandos (dia 7)
Route::get('/devices/{device_id}/commands', [CommandController::class, 'pending']);
Route::patch('/devices/{device_id}/commands/{command_id}/ack', [CommandController::class, 'ack']);
Route::post('/commands', [CommandController::class, 'store']);

// Alertas (dia 8)
Route::post('/alert-rules', [AlertController::class, 'storeRule'])->name('alert-rules.store');
Route::get('/alert-rules', [AlertController::class, 'listRules'])->name('alert-rules.list');
Route::delete('/alert-rules/{id}', [AlertController::class, 'disableRule'])->name('alert-rules.disable');
Route::get('/alerts', [AlertController::class, 'listAlerts'])->name('alerts.list');
Route::patch('/alerts/{id}/resolve', [AlertController::class, 'resolveAlert'])->name('alerts.resolve');

// Ruta de ejemplo de Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

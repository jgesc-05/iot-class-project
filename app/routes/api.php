<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MetricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/metrics', [MetricController::class, 'store']);

Route::get('/devices/{device_id}/latest', [DeviceController::class, 'latest']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

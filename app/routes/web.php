<?php

use Illuminate\Support\Facades\Route;

// Redirige la landing segun autenticacion
Route::get('/', function () {
    return auth()->check()
        ? redirect('/dashboard')
        : redirect('/login');
});

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

//Rutas para dispositivos
Route::get('/devices', \App\Livewire\DeviceList::class)
    ->middleware(['auth', 'verified'])
    ->name('devices.index');

//Vista para crear dispositivo
Route::get('/devices/create', App\Livewire\DeviceCreate::class)
    ->middleware('auth')
    ->name('devices.create');


Route::get('/devices/{deviceId}', App\Livewire\DeviceDetail::class)
    ->middleware('auth')
    ->name('devices.show');

//Vista de reglas
Route::get('/alert-rules', App\Livewire\RulesManager::class)
    ->middleware('auth')
    ->name('rules.index');

//Vista de marcar como resuelta la alerta
Route::get('/alerts', App\Livewire\AlertList::class)
    ->middleware('auth')
    ->name('alerts.index');

//Vista de historial con filtros (Dia 10 A.10.1)
Route::get('/history', App\Livewire\History::class)
    ->middleware('auth')
    ->name('history.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

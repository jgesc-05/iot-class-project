<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

//Rutas para dispositivos
Route::get('/devices', \App\Livewire\DeviceList::class)
    ->middleware(['auth', 'verified'])
    ->name('devices.index');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Crear un usuario y un dispositivo de prueba
    $this->user = User::factory()->create();
    $this->device = Device::create([
        'user_id' => $this->user->id,
        'name' => 'Test Device',
        'device_id' => 'test-device-latest',
        'type' => 'real',
        'measurement' => 'temperatura_ambiente',
        'unit' => '°C',
        'api_key_hash' => hash('sha256', 'test-key'),
        'sample_interval_s' => 15,
    ]);
});

test('devuelve la ultima metrica de un dispositivo con datos', function () {
    // Insertar 3 métricas con timestamps separados
    $now = now();
    DB::table('metrics')->insert([
        [
            'time' => $now->copy()->subMinutes(2),
            'device_id' => 'test-device-latest',
            'value' => 20.0,
            'metadata' => null,
        ],
        [
            'time' => $now->copy()->subMinute(),
            'device_id' => 'test-device-latest',
            'value' => 21.5,
            'metadata' => null,
        ],
        [
            'time' => $now,
            'device_id' => 'test-device-latest',
            'value' => 22.7,
            'metadata' => null,
        ],
    ]);

    $response = $this->getJson('/api/devices/test-device-latest/latest');

    $response->assertStatus(200);
    $response->assertJson([
        'device_id' => 'test-device-latest',
        'value' => 22.7,
    ]);
    expect($response->json('time'))->not->toBeNull();
});

test('devuelve 200 con value null si el dispositivo existe pero no tiene metricas', function () {
    $response = $this->getJson('/api/devices/test-device-latest/latest');

    $response->assertStatus(200);
    $response->assertJson([
        'device_id' => 'test-device-latest',
        'value' => null,
        'time' => null,
        'metadata' => null,
    ]);
});

test('devuelve 404 si el dispositivo no existe', function () {
    $response = $this->getJson('/api/devices/dispositivo-fantasma/latest');

    $response->assertStatus(404);
    $response->assertJson([
        'error' => 'device_not_found',
    ]);
});

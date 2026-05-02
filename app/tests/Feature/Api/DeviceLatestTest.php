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

test('GET metrics devuelve metricas en el rango especificado', function () {
    $now = now();
    DB::table('metrics')->insert([
        ['time' => $now->copy()->subHours(5), 'device_id' => 'test-device-latest', 'value' => 10.0, 'metadata' => null],
        ['time' => $now->copy()->subHours(3), 'device_id' => 'test-device-latest', 'value' => 20.0, 'metadata' => null],
        ['time' => $now->copy()->subHour(),   'device_id' => 'test-device-latest', 'value' => 30.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(30), 'device_id' => 'test-device-latest', 'value' => 40.0, 'metadata' => null],
        ['time' => $now,                        'device_id' => 'test-device-latest', 'value' => 50.0, 'metadata' => null],
    ]);

    // Pedir las metricas de las ultimas 2h: deberia traer las de -1h, -30min y now (3 filas)
    $from = $now->copy()->subHours(2)->toIso8601String();
    $to = $now->copy()->addMinute()->toIso8601String();

    $query = http_build_query(["from" => $from, "to" => $to]);
    $response = $this->getJson("/api/devices/test-device-latest/metrics?" . $query);

    $response->assertStatus(200);
    $response->assertJsonPath('count', 3);
    $response->assertJsonPath('device_id', 'test-device-latest');
    expect($response->json('metrics'))->toHaveCount(3);
});

test('GET metrics usa default 2h cuando no se manda from/to', function () {
    $now = now();
    DB::table('metrics')->insert([
        ['time' => $now->copy()->subHours(5), 'device_id' => 'test-device-latest', 'value' => 10.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(30), 'device_id' => 'test-device-latest', 'value' => 20.0, 'metadata' => null],
    ]);

    $response = $this->getJson('/api/devices/test-device-latest/metrics');

    $response->assertStatus(200);
    $response->assertJsonPath('count', 1);  // solo la de -30min cae en las ultimas 2h
});

test('GET metrics devuelve 422 si from es timestamp invalido', function () {
    $response = $this->getJson('/api/devices/test-device-latest/metrics?from=fecha-fea');

    $response->assertStatus(422);
    $response->assertJsonPath('error', 'invalid_timestamp');
});

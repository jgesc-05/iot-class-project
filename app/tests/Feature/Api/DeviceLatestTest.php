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

test('GET stats devuelve avg/min/max/count correctamente', function () {
    $now = now();
    DB::table('metrics')->insert([
        ['time' => $now->copy()->subHour(),       'device_id' => 'test-device-latest', 'value' => 10.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(50),  'device_id' => 'test-device-latest', 'value' => 20.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(40),  'device_id' => 'test-device-latest', 'value' => 30.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(30),  'device_id' => 'test-device-latest', 'value' => 40.0, 'metadata' => null],
        ['time' => $now->copy()->subMinutes(20),  'device_id' => 'test-device-latest', 'value' => 50.0, 'metadata' => null],
    ]);

    // 5 valores: 10, 20, 30, 40, 50
    // avg = 30, min = 10, max = 50, count = 5
    $response = $this->getJson('/api/devices/test-device-latest/stats');

    $response->assertStatus(200);
    $response->assertJsonPath('stats.avg', 30);
    $response->assertJsonPath('stats.min', 10);
    $response->assertJsonPath('stats.max', 50);
    $response->assertJsonPath('stats.count', 5);
});

test('GET stats devuelve null/0 si no hay metricas en el rango', function () {
    $response = $this->getJson('/api/devices/test-device-latest/stats');

    $response->assertStatus(200);
    $response->assertJsonPath('stats.avg', null);
    $response->assertJsonPath('stats.min', null);
    $response->assertJsonPath('stats.max', null);
    $response->assertJsonPath('stats.count', 0);
});

test('GET stats devuelve 422 si from es timestamp invalido', function () {
    $response = $this->getJson('/api/devices/test-device-latest/stats?from=fecha-fea');

    $response->assertStatus(422);
    $response->assertJsonPath('error', 'invalid_timestamp');
});

test('GET history agrupa metricas por bucket con avg/min/max', function () {
    // Usamos timestamps absolutos para que el test sea determinista.
    // time_bucket() agrupa por minutos absolutos del reloj (:00, :05, :10),
    // no por offsets relativos a now().
    $base = \Carbon\Carbon::parse('2026-01-15 12:00:00');
    DB::table('metrics')->insert([
        // Bucket 12:00 → avg(10, 20) = 15
        ['time' => $base->copy()->addMinutes(0)->addSeconds(0),  'device_id' => 'test-device-latest', 'value' => 10.0, 'metadata' => null],
        ['time' => $base->copy()->addMinutes(2)->addSeconds(30), 'device_id' => 'test-device-latest', 'value' => 20.0, 'metadata' => null],
        // Bucket 12:05 → avg(30, 40) = 35
        ['time' => $base->copy()->addMinutes(5)->addSeconds(15), 'device_id' => 'test-device-latest', 'value' => 30.0, 'metadata' => null],
        ['time' => $base->copy()->addMinutes(8)->addSeconds(45), 'device_id' => 'test-device-latest', 'value' => 40.0, 'metadata' => null],
    ]);

    // Pedir bucket de 5 minutos sobre el rango que cubre las 4 metricas
    $response = $this->getJson('/api/devices/test-device-latest/history?bucket=5m&' . http_build_query([
        'from' => $base->copy()->subMinutes(10)->toIso8601String(),
        'to' => $base->copy()->addMinutes(20)->toIso8601String(),
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('bucket', '5 minutes');
    expect($response->json('count'))->toBe(2);

    $history = $response->json('history');
    expect($history[0]['avg'])->toBe(15);
    expect($history[0]['min'])->toBe(10);
    expect($history[0]['max'])->toBe(20);
    expect($history[1]['avg'])->toBe(35);
});

test('GET history elige bucket auto segun tamano del rango', function () {
    // Rango chico (1h) → debe usar 5 minutos
    $response = $this->getJson('/api/devices/test-device-latest/history?bucket=auto&' . http_build_query([
        'from' => now()->subHour()->toIso8601String(),
        'to' => now()->toIso8601String(),
    ]));
    $response->assertJsonPath('bucket', '5 minutes');

    // Rango medio (3 dias) → debe usar 1 hora
    $response = $this->getJson('/api/devices/test-device-latest/history?bucket=auto&' . http_build_query([
        'from' => now()->subDays(3)->toIso8601String(),
        'to' => now()->toIso8601String(),
    ]));
    $response->assertJsonPath('bucket', '1 hour');

    // Rango grande (30 dias) → debe usar 1 dia
    $response = $this->getJson('/api/devices/test-device-latest/history?bucket=auto&' . http_build_query([
        'from' => now()->subDays(30)->toIso8601String(),
        'to' => now()->toIso8601String(),
    ]));
    $response->assertJsonPath('bucket', '1 day');
});

test('GET history devuelve 422 si bucket es invalido', function () {
    $response = $this->getJson('/api/devices/test-device-latest/history?bucket=2y');

    $response->assertStatus(422);
    $response->assertJsonPath('error', 'invalid_bucket');
});

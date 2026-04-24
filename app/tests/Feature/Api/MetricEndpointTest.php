<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Helper: crea un dispositivo válido y devuelve [device, plainKey].
 * Generamos la key en plano y guardamos su hash — igual que hace el seeder real.
 */
function makeDevice(array $overrides = []): array
{
    $plainKey = 'dk_'.bin2hex(random_bytes(8));

    $device = Device::create(array_merge([
        'user_id'      => User::factory()->create()->id,
        'name'         => 'Test DHT22',
        'device_id'    => 'test-sensor-'.uniqid(),
        'type'         => 'real',
        'measurement'  => 'temperatura_ambiente',
        'unit'         => '°C',
        'api_key_hash' => hash('sha256', $plainKey),
    ], $overrides));

    return [$device, $plainKey];
}


test('acepta una metrica valida y la persiste en la hypertable', function () {
    [$device, $plainKey] = makeDevice();

    $payload = [
        'device_id'   => $device->device_id,
        'api_key'     => $plainKey,
        'measurement' => 'temperatura_ambiente',
        'value'       => 24.5,
        'unit'        => '°C',
    ];

    $response = $this->postJson('/api/metrics', $payload);

    $response->assertStatus(201)
             ->assertJson(['accepted' => true]);

    // Verificar que se insertó exactamente 1 fila en metrics
    expect(DB::table('metrics')->count())->toBe(1);

    $metric = DB::table('metrics')->first();
    expect((float) $metric->value)->toBe(24.5);
    expect($metric->device_id)->toBe($device->device_id);
});


test('rechaza con 401 cuando la api_key es incorrecta', function () {
    [$device, $plainKey] = makeDevice();

    $response = $this->postJson('/api/metrics', [
        'device_id'   => $device->device_id,
        'api_key'     => 'dk_esto_no_coincide',
        'measurement' => 'temperatura_ambiente',
        'value'       => 22.0,
    ]);

    $response->assertStatus(401)
             ->assertJson(['error' => 'invalid_api_key']);

    // Importante: nada debe haberse insertado
    expect(DB::table('metrics')->count())->toBe(0);
});


test('rechaza con 422 cuando falta un campo requerido', function () {
    $response = $this->postJson('/api/metrics', [
        // Sin device_id, api_key, measurement ni value
    ]);

    $response->assertStatus(422);

    // El body debe tener errores para los 4 campos requeridos
    $response->assertJsonValidationErrors([
        'device_id', 'api_key', 'measurement', 'value',
    ]);

    expect(DB::table('metrics')->count())->toBe(0);
});

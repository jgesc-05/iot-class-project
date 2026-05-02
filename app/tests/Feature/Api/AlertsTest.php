<?php

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plainKey = 'dk_test_alert_endpoint';
    $this->device = Device::create([
        'user_id' => $this->user->id,
        'name' => 'Test Device',
        'device_id' => 'test-device-alert',
        'type' => 'real',
        'measurement' => 'temperatura_ambiente',
        'unit' => '°C',
        'api_key_hash' => hash('sha256', $this->plainKey),
        'sample_interval_s' => 15,
    ]);
});

test('POST alert-rules crea una regla con nombre y thresholds', function () {
    $response = $this->postJson('/api/alert-rules', [
        'name' => 'Temperatura critica',
        'device_id' => 'test-device-alert',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('name', 'Temperatura critica');
    $response->assertJsonPath('enabled', true);

    $this->assertDatabaseHas('alert_rules', [
        'device_id' => $this->device->id,
        'name' => 'Temperatura critica',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
    ]);
});

test('POST alert-rules sin thresholds devuelve 422', function () {
    $response = $this->postJson('/api/alert-rules', [
        'name' => 'Sin thresholds',
        'device_id' => 'test-device-alert',
        'measurement' => 'temperatura_ambiente',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error', 'invalid_thresholds');
});

test('metrica fuera de rango crea alerta automaticamente', function () {
    $rule = AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => true,
    ]);

    // Enviar metrica con valor fuera de rango
    $response = $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 35.0,
        'unit' => '°C',
        'timestamp' => now()->toIso8601String(),
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('alerts', [
        'alert_rule_id' => $rule->id,
        'device_id' => $this->device->id,
        'value' => 35.0,
    ]);
});

test('metrica dentro de rango NO crea alerta', function () {
    AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => true,
    ]);

    $response = $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 22.0,
        'unit' => '°C',
        'timestamp' => now()->toIso8601String(),
    ]);

    $response->assertStatus(201);

    expect(Alert::where('device_id', $this->device->id)->count())->toBe(0);
});

test('regla deshabilitada no crea alerta aunque la metrica este fuera de rango', function () {
    AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => false,  // ← deshabilitada
    ]);

    $response = $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 50.0,
        'unit' => '°C',
        'timestamp' => now()->toIso8601String(),
    ]);

    $response->assertStatus(201);
    expect(Alert::where('device_id', $this->device->id)->count())->toBe(0);
});

test('deduplicacion: no crea segunda alerta si hay una pendiente', function () {
    $rule = AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => true,
    ]);

    // Primera metrica fuera de rango
    $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 35.0,
        'unit' => '°C',
        'timestamp' => now()->toIso8601String(),
    ]);

    // Segunda metrica fuera de rango (debe ser ignorada por deduplicacion)
    $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 40.0,
        'unit' => '°C',
        'timestamp' => now()->addSecond()->toIso8601String(),
    ]);

    expect(Alert::where('alert_rule_id', $rule->id)->count())->toBe(1);
});

test('PATCH resolve marca la alerta como resuelta', function () {
    $rule = AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => true,
    ]);

    $alert = Alert::create([
        'alert_rule_id' => $rule->id,
        'device_id' => $this->device->id,
        'value' => 35.0,
        'triggered_at' => now(),
    ]);

    $response = $this->patchJson("/api/alerts/{$alert->id}/resolve");

    $response->assertStatus(204);

    $alert->refresh();
    expect($alert->resolved_at)->not->toBeNull();
});

test('metrica dentro de rango auto-resuelve alerta pendiente', function () {
    $rule = AlertRule::create([
        'device_id' => $this->device->id,
        'name' => 'Temp alta',
        'measurement' => 'temperatura_ambiente',
        'min_threshold' => 18.0,
        'max_threshold' => 28.0,
        'enabled' => true,
    ]);

    // Crear alerta pendiente manualmente (simula que se disparo antes)
    $alert = Alert::create([
        'alert_rule_id' => $rule->id,
        'device_id' => $this->device->id,
        'value' => 35.0,
        'triggered_at' => now()->subMinutes(5),
    ]);

    expect($alert->resolved_at)->toBeNull();

    // Enviar metrica DENTRO del rango (debe auto-resolver)
    $response = $this->postJson('/api/metrics', [
        'device_id' => 'test-device-alert',
        'api_key' => $this->plainKey,
        'measurement' => 'temperatura_ambiente',
        'value' => 22.0,
        'unit' => '°C',
        'timestamp' => now()->toIso8601String(),
    ]);

    $response->assertStatus(201);

    $alert->refresh();
    expect($alert->resolved_at)->not->toBeNull();
});

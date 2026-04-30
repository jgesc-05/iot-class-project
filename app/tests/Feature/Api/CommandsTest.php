<?php

use App\Models\Command;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plainKey = 'dk_test_command_endpoint';
    $this->device = Device::create([
        'user_id' => $this->user->id,
        'name' => 'Test Device',
        'device_id' => 'test-device-cmd',
        'type' => 'real',
        'measurement' => 'temperatura_ambiente',
        'unit' => '°C',
        'api_key_hash' => hash('sha256', $this->plainKey),
        'sample_interval_s' => 15,
    ]);
});

test('GET commands devuelve los pendientes con bearer correcto', function () {
    Command::create([
        'device_id' => $this->device->id,
        'type' => 'set_interval',
        'payload' => ['seconds' => 5],
        'status' => 'pending',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->plainKey,
    ])->getJson('/api/devices/test-device-cmd/commands');

    $response->assertStatus(200);
    $response->assertJsonCount(1, 'commands');
    $response->assertJsonPath('commands.0.type', 'set_interval');
});

test('GET commands rechaza con 401 si bearer es invalido', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer dk_token-falso',
    ])->getJson('/api/devices/test-device-cmd/commands');

    $response->assertStatus(401);
    $response->assertJsonPath('error', 'invalid_bearer_token');
});

test('PATCH ack cambia status a executed y registra acked_at', function () {
    $command = Command::create([
        'device_id' => $this->device->id,
        'type' => 'on_off',
        'payload' => ['on' => true],
        'status' => 'pending',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->plainKey,
    ])->patchJson("/api/devices/test-device-cmd/commands/{$command->id}/ack", [
        'status' => 'executed',
    ]);

    $response->assertStatus(204);

    $command->refresh();
    expect($command->status)->toBe('executed');
    expect($command->acked_at)->not->toBeNull();
});

test('PATCH ack a comando ya executed devuelve 409', function () {
    $command = Command::create([
        'device_id' => $this->device->id,
        'type' => 'on_off',
        'payload' => ['on' => true],
        'status' => 'executed',
        'acked_at' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->plainKey,
    ])->patchJson("/api/devices/test-device-cmd/commands/{$command->id}/ack", [
        'status' => 'executed',
    ]);

    $response->assertStatus(409);
    $response->assertJsonPath('error', 'command_already_acked');
});

test('POST commands crea un comando pending con tipo valido', function () {
    $response = $this->postJson('/api/commands', [
        'device_id' => 'test-device-cmd',
        'type' => 'calibrate_offset',
        'payload' => ['offset' => 0.3],
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'pending');
    $response->assertJsonPath('type', 'calibrate_offset');

    $this->assertDatabaseHas('commands', [
        'device_id' => $this->device->id,
        'type' => 'calibrate_offset',
        'status' => 'pending',
    ]);
});

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Device;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CommandController extends Controller
{
    /**
     * GET /api/devices/{device_id}/commands
     */
    public function pending(Request $request, string $deviceId): JsonResponse
    {
        $device = $this->findDeviceOrFail($deviceId);
        $this->validateBearer($request, $device);

        $commands = Command::where('device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get(['id', 'type', 'payload', 'created_at']);

        return response()->json(['commands' => $commands]);
    }

    /**
     * PATCH /api/devices/{device_id}/commands/{command_id}/ack
     */
    public function ack(Request $request, string $deviceId, int $commandId): Response
    {
        $device = $this->findDeviceOrFail($deviceId);
        $this->validateBearer($request, $device);

        $command = Command::where('device_id', $device->id)
            ->where('id', $commandId)
            ->first();

        if (!$command) {
            $this->jsonError(404, 'command_not_found',
                "El comando {$commandId} no existe para el dispositivo '{$deviceId}'.");
        }

        if ($command->status !== 'pending') {
            $this->jsonError(409, 'command_already_acked',
                "El comando {$commandId} ya fue marcado como {$command->status}.");
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['executed', 'failed'])],
            'result' => ['nullable', 'array'],
        ]);

        $command->update([
            'status' => $validated['status'],
            'acked_at' => now(),
            'result' => $validated['result'] ?? null,
        ]);

        return response()->noContent();
    }

    /**
     * POST /api/commands
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'exists:devices,device_id'],
            'type' => ['required', Rule::in(['on_off', 'set_interval', 'calibrate_offset'])],
            'payload' => ['nullable', 'array'],
        ]);

        $device = Device::where('device_id', $validated['device_id'])->firstOrFail();

        $command = Command::create([
            'device_id' => $device->id,
            'type' => $validated['type'],
            'payload' => $validated['payload'] ?? [],
            'status' => 'pending',
        ]);

        return response()->json([
            'id' => $command->id,
            'device_id' => $device->device_id,
            'type' => $command->type,
            'payload' => $command->payload,
            'status' => $command->status,
            'created_at' => $command->created_at,
        ], 201);
    }

    private function findDeviceOrFail(string $deviceId): Device
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            $this->jsonError(404, 'device_not_found',
                "El device_id '{$deviceId}' no existe.");
        }

        return $device;
    }

    private function validateBearer(Request $request, Device $device): void
    {
        $token = $request->bearerToken();

        if (!$token || !hash_equals($device->api_key_hash, hash('sha256', $token))) {
            $this->jsonError(401, 'invalid_bearer_token',
                'El token Bearer no coincide con la API key del dispositivo.');
        }
    }

    /**
     * Lanza una respuesta JSON limpia con el status code dado.
     * Usa HttpResponseException porque devuelve la respuesta al cliente
     * sin que Laravel agregue stack trace ni metadata.
     */
    private function jsonError(int $status, string $error, string $message): never
    {
        throw new HttpResponseException(
            response()->json([
                'error' => $error,
                'message' => $message,
            ], $status)
        );
    }
}

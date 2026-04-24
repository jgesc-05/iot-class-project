<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

class MetricController extends Controller
{
    /**
     * POST /api/metrics
     *
     * Recibe una métrica de un dispositivo. Flujo:
     *  1. StoreMetricRequest valida la forma del payload.
     *  2. Aquí validamos la API key contra el hash guardado.
     *  3. (Tarea 3.4) Insertamos la métrica en la hypertable.
     */
    public function store(StoreMetricRequest $request): JsonResponse
    {
        $data = $request->validated();

        $device = Device::where('device_id', $data['device_id'])->first();

        // Validación de API key: comparamos hash con hash, en tiempo constante.
        $providedHash = hash('sha256', $data['api_key']);

        if (!hash_equals($device->api_key_hash, $providedHash)) {
            return response()->json([
                'error' => 'invalid_api_key',
                'message' => 'La API key no corresponde al device_id.',
            ], 401);
        }

        // Si el dispositivo está inactivo, también rechazamos.
        if ($device->status !== 'active') {
            return response()->json([
                'error' => 'device_inactive',
                'message' => 'Dispositivo desactivado.',
            ], 403);
        }

        // Por ahora solo confirmamos. La inserción en la hypertable viene en 3.4.
        return response()->json([
            'accepted' => true,
            'message' => 'autenticación OK — falta persistir en metrics',
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'name' => $device->name,
            ],
        ], 201);
    }
}

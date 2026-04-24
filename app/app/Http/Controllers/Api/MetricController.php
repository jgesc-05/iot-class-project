<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetricController extends Controller
{
    /**
     * POST /api/metrics
     *
     * Recibe una métrica de un dispositivo y la persiste en la
     * hypertable `metrics`. Flujo:
     *  1. StoreMetricRequest valida la forma del payload.
     *  2. Validamos la API key contra el hash guardado.
     *  3. Insertamos en la hypertable con DB::table.
     */
    public function store(StoreMetricRequest $request): JsonResponse
    {
        $data = $request->validated();

        $device = Device::where('device_id', $data['device_id'])->first();

        // Validación de API key.
        $providedHash = hash('sha256', $data['api_key']);

        if (!hash_equals($device->api_key_hash, $providedHash)) {
            return response()->json([
                'error' => 'invalid_api_key',
                'message' => 'La API key no corresponde al device_id.',
            ], 401);
        }

        if ($device->status !== 'active') {
            return response()->json([
                'error' => 'device_inactive',
                'message' => 'Dispositivo desactivado.',
            ], 403);
        }

        // Determinar el timestamp: si el cliente lo mandó, usarlo; si no, ahora.
        $time = isset($data['timestamp'])
            ? Carbon::parse($data['timestamp'])
            : now();

        // Insertar en la hypertable.
        DB::table('metrics')->insert([
            'time'      => $time,
            'device_id' => $device->device_id,
            'value'     => $data['value'],
            'metadata'  => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);

        return response()->json([
            'accepted' => true,
            'server_time' => now()->toIso8601String(),
            'stored_at' => $time->toIso8601String(),
        ], 201);
    }
}

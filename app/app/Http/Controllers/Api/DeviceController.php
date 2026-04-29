<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * GET /api/devices/{device_id}/latest
     *
     * Devuelve la última métrica registrada para un dispositivo.
     * Endpoint público de solo lectura — útil para digital twins,
     * detalle de dispositivo en UI, e integraciones externas.
     */
    public function latest(string $deviceId): JsonResponse
    {
        // 1. Validar que el dispositivo existe (404 si no).
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'device_not_found',
                'message' => "El device_id '{$deviceId}' no existe.",
            ], 404);
        }

        // 2. Buscar la última métrica.
        $metric = DB::table('metrics')
            ->where('device_id', $deviceId)
            ->orderByDesc('time')
            ->first();

        // 3. Si el dispositivo existe pero no ha enviado métricas aún:
        //    devolver 200 con value null (no es un error, es "aún sin datos").
        if (!$metric) {
            return response()->json([
                'device_id' => $deviceId,
                'value' => null,
                'time' => null,
                'metadata' => null,
            ]);
        }

        // 4. Caso normal: devolver la última métrica.
        return response()->json([
            'device_id' => $metric->device_id,
            'value' => (float) $metric->value,
            'time' => $metric->time,
            'metadata' => $metric->metadata
                ? json_decode($metric->metadata, true)
                : null,
        ]);
    }
}

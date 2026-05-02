<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * GET /api/devices/{device_id}/latest
     *
     * Devuelve la última métrica registrada para un dispositivo.
     * Endpoint público de solo lectura.
     */
    public function latest(string $deviceId): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'device_not_found',
                'message' => "El device_id '{$deviceId}' no existe.",
            ], 404);
        }

        $metric = DB::table('metrics')
            ->where('device_id', $deviceId)
            ->orderByDesc('time')
            ->first();

        if (!$metric) {
            return response()->json([
                'device_id' => $deviceId,
                'value' => null,
                'time' => null,
                'metadata' => null,
            ]);
        }

        return response()->json([
            'device_id' => $metric->device_id,
            'value' => (float) $metric->value,
            'time' => $metric->time,
            'metadata' => $metric->metadata
                ? json_decode($metric->metadata, true)
                : null,
        ]);
    }

    /**
     * GET /api/devices/{device_id}/metrics?from=...&to=...&limit=...
     *
     * Devuelve metricas historicas en un rango temporal. Util para
     * alimentar mini-graficos en la vista de detalle de dispositivo.
     *
     * Query params (todos opcionales):
     *   - from: timestamp ISO 8601. Default: hace 2 horas.
     *   - to:   timestamp ISO 8601. Default: ahora.
     *   - limit: maximo de filas. Default 500, maximo 5000.
     */
    public function metrics(Request $request, string $deviceId): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'device_not_found',
                'message' => "El device_id '{$deviceId}' no existe.",
            ], 404);
        }

        // Parsear rango temporal (con defaults sanos).
        try {
            $from = $request->query('from')
                ? Carbon::parse($request->query('from'))
                : now()->subHours(2);
            $to = $request->query('to')
                ? Carbon::parse($request->query('to'))
                : now();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid_timestamp',
                'message' => 'Los parametros from/to deben ser timestamps ISO 8601 validos.',
            ], 422);
        }

        // Limit con tope defensivo.
        $limit = min((int) $request->query('limit', 500), 5000);

        $metrics = DB::table('metrics')
            ->where('device_id', $deviceId)
            ->whereBetween('time', [$from, $to])
            ->orderBy('time')
            ->limit($limit)
            ->get(['time', 'value']);

        return response()->json([
            'device_id' => $deviceId,
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'count' => $metrics->count(),
            'metrics' => $metrics->map(fn($m) => [
                'time' => $m->time,
                'value' => (float) $m->value,
            ]),
        ]);
    }
}

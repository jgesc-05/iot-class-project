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

    /**
     * GET /api/devices/{device_id}/stats?from=...&to=...
     *
     * Devuelve estadisticas agregadas (avg, min, max, count) de las
     * metricas de un dispositivo en un rango temporal. Util para las
     * cards de "Estadisticas del periodo" (A.10.2).
     *
     * Query params (todos opcionales):
     *   - from: timestamp ISO 8601. Default: hace 24 horas.
     *   - to:   timestamp ISO 8601. Default: ahora.
     *
     * Si no hay metricas en el rango, devuelve count=0 y avg/min/max=null
     * en lugar de error 404 (un rango vacio no es un error, es informacion).
     */
    public function stats(Request $request, string $deviceId): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();
        if (!$device) {
            return response()->json([
                'error' => 'device_not_found',
                'message' => "El device_id '{$deviceId}' no existe.",
            ], 404);
        }

        // Parsear rango temporal (default 24h).
        try {
            $from = $request->query('from')
                ? Carbon::parse($request->query('from'))
                : now()->subDay();
            $to = $request->query('to')
                ? Carbon::parse($request->query('to'))
                : now();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid_timestamp',
                'message' => 'Los parametros from/to deben ser timestamps ISO 8601 validos.',
            ], 422);
        }

        // Una sola query con AVG/MIN/MAX/COUNT (PostgreSQL las computa de un solo escaneo).
        $row = DB::table('metrics')
            ->where('device_id', $deviceId)
            ->whereBetween('time', [$from, $to])
            ->selectRaw('AVG(value) AS avg, MIN(value) AS min, MAX(value) AS max, COUNT(*) AS count')
            ->first();

        return response()->json([
            'device_id' => $deviceId,
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'stats' => [
                'avg'   => $row->avg !== null ? round((float) $row->avg, 2) : null,
                'min'   => $row->min !== null ? (float) $row->min : null,
                'max'   => $row->max !== null ? (float) $row->max : null,
                'count' => (int) $row->count,
            ],
        ]);
    }

    /**
     * GET /api/devices/{device_id}/history?from=...&to=...&bucket=...
     *
     * Devuelve metricas agregadas por intervalo de tiempo (avg/min/max
     * por bucket). Util para graficos de historial donde mostrar todos
     * los puntos crudos seria demasiado denso.
     *
     * Usa la funcion time_bucket() de TimescaleDB que es similar a
     * date_trunc() pero mucho mas eficiente sobre hypertables.
     *
     * Query params (todos opcionales):
     *   - from: timestamp ISO 8601. Default: hace 24 horas.
     *   - to:   timestamp ISO 8601. Default: ahora.
     *   - bucket: '5m'|'1h'|'1d'|'auto'. Default: 'auto'.
     *     Auto: 5m si rango <= 6h, 1h si rango <= 7d, 1d si mas.
     */
    public function history(Request $request, string $deviceId): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();
        if (!$device) {
            return response()->json([
                'error' => 'device_not_found',
                'message' => "El device_id '{$deviceId}' no existe.",
            ], 404);
        }

        // Parsear rango temporal (default 24h).
        try {
            $from = $request->query('from')
                ? Carbon::parse($request->query('from'))
                : now()->subDay();
            $to = $request->query('to')
                ? Carbon::parse($request->query('to'))
                : now();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid_timestamp',
                'message' => 'Los parametros from/to deben ser timestamps ISO 8601 validos.',
            ], 422);
        }

        // Determinar el bucket. 'auto' lo elige segun el tamano del rango.
        $bucketParam = $request->query('bucket', 'auto');
        $rangeHours = $from->diffInHours($to);

        $bucketSql = match($bucketParam) {
            '5m' => '5 minutes',
            '1h' => '1 hour',
            '1d' => '1 day',
            'auto' => $rangeHours <= 6 ? '5 minutes' : ($rangeHours <= 168 ? '1 hour' : '1 day'),
            default => null,
        };

        if ($bucketSql === null) {
            return response()->json([
                'error' => 'invalid_bucket',
                'message' => "El parametro bucket debe ser uno de: 5m, 1h, 1d, auto.",
            ], 422);
        }

        // time_bucket() es funcion de TimescaleDB. Agrupa los time en buckets
        // del tamano dado. Calcula avg/min/max por bucket en una sola query.
        $rows = DB::select(
            "SELECT
                time_bucket(?::interval, time) AS bucket_time,
                AVG(value) AS avg,
                MIN(value) AS min,
                MAX(value) AS max
            FROM metrics
            WHERE device_id = ? AND time BETWEEN ? AND ?
            GROUP BY bucket_time
            ORDER BY bucket_time",
            [$bucketSql, $deviceId, $from, $to]
        );

        return response()->json([
            'device_id' => $deviceId,
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'bucket' => $bucketSql,
            'count' => count($rows),
            'history' => array_map(fn($r) => [
                'time' => $r->bucket_time,
                'avg' => round((float) $r->avg, 2),
                'min' => (float) $r->min,
                'max' => (float) $r->max,
            ], $rows),
        ]);
    }
}

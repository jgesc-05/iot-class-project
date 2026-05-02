<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetricController extends Controller
{
    /**
     * POST /api/metrics
     *
     * Recibe una metrica de un dispositivo y la persiste en la
     * hypertable `metrics`. Flujo:
     *  1. StoreMetricRequest valida la forma del payload.
     *  2. Validamos la API key contra el hash guardado.
     *  3. Insertamos en la hypertable con DB::table.
     *  4. Evaluamos reglas de alerta activas (graceful: no bloquea si falla).
     */
    public function store(StoreMetricRequest $request): JsonResponse
    {
        $data = $request->validated();
        $device = Device::where('device_id', $data['device_id'])->first();

        // Validacion de API key.
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

        // Determinar el timestamp: si el cliente lo mando, usarlo; si no, ahora.
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

        // NUEVO dia 8: evaluar reglas activas y crear alertas si corresponde.
        // Envuelto en try/catch para graceful degradation: si la evaluacion
        // falla por un bug, la metrica se guarda igual y solo logueamos.
        try {
            $this->evaluateRules($device, $data['measurement'], (float) $data['value']);
        } catch (\Throwable $e) {
            Log::error('Error evaluando reglas de alerta', [
                'device_id' => $device->device_id,
                'measurement' => $data['measurement'],
                'value' => $data['value'],
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'accepted' => true,
            'server_time' => now()->toIso8601String(),
            'stored_at' => $time->toIso8601String(),
        ], 201);
    }

    /**
     * Evalua reglas activas para el dispositivo y measurement dados.
     * Si una regla detecta valor fuera de rango Y no hay alerta pendiente
     * de la misma regla, crea una nueva alerta.
     */
    private function evaluateRules(Device $device, string $measurement, float $value): void
    {
        $rules = AlertRule::where('device_id', $device->id)
            ->where('measurement', $measurement)
            ->where('enabled', true)
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->isOutOfRange($value)) {
                continue;
            }

            // Deduplicacion: si ya hay alerta pendiente de esta regla, no creamos otra.
            $hasPending = Alert::where('alert_rule_id', $rule->id)
                ->whereNull('resolved_at')
                ->exists();

            if ($hasPending) {
                continue;
            }

            Alert::create([
                'alert_rule_id' => $rule->id,
                'device_id' => $device->id,
                'value' => $value,
                'triggered_at' => now(),
            ]);
        }
    }
}

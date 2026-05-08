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

        $time = isset($data['timestamp'])
            ? Carbon::parse($data['timestamp'])
            : now();

        DB::table('metrics')->insert([
            'time'      => $time,
            'device_id' => $device->device_id,
            'value'     => $data['value'],
            'metadata'  => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);

        // Evaluar reglas activas: dispara alertas si valor fuera de rango.
        // Graceful degradation: si falla, la metrica se guarda igual.
        try {
            $this->evaluateRules($device, $data['measurement'], (float) $data['value'], $time);
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
     *
     * - Si valor esta fuera de rango Y no hay alerta pendiente: crea alerta nueva.
     * - Las alertas solo se resuelven manualmente por el operador
     *   via PATCH /api/alerts/{id}/resolve.
     */
    private function evaluateRules(Device $device, string $measurement, float $value, Carbon $time): void
    {
        $rules = AlertRule::where('device_id', $device->id)
            ->where('measurement', $measurement)
            ->where('enabled', true)
            ->get();

        foreach ($rules as $rule) {
            $openAlert = Alert::where('alert_rule_id', $rule->id)
                ->whereNull('resolved_at')
                ->first();

            if ($rule->isOutOfRange($value) && !$openAlert) {
                // Fuera de rango y sin alerta pendiente: crear alerta nueva.
                // Las alertas solo se resuelven manualmente por el operador.
                Alert::create([
                    'alert_rule_id' => $rule->id,
                    'device_id' => $device->id,
                    'value' => $value,
                    'triggered_at' => $time,
                ]);
            }
        }
    }
}

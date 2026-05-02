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

        // Evaluar reglas activas: dispara alertas si valor fuera de rango,
        // las auto-resuelve si valor vuelve dentro de rango.
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
     * - Si valor esta dentro de rango Y hay alerta pendiente: la resuelve.
     * - En cualquier otro caso, no hace nada.
     *
     * Esto modela el ciclo de vida natural de una alerta: se dispara cuando
     * aparece la condicion, se resuelve cuando desaparece. El operador puede
     * intervenir manualmente con PATCH /api/alerts/{id}/resolve si quiere
     * resolver antes (o cerrar una alerta sin que el dispositivo haya
     * vuelto al rango).
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

            if ($rule->isOutOfRange($value)) {
                // Fuera de rango: crear alerta nueva solo si no hay una pendiente
                if (!$openAlert) {
                    Alert::create([
                        'alert_rule_id' => $rule->id,
                        'device_id' => $device->id,
                        'value' => $value,
                        'triggered_at' => $time,
                    ]);
                }
            } else {
                // Dentro de rango: auto-resolver alerta pendiente si la habia
                if ($openAlert) {
                    $openAlert->resolve($time);
                }
            }
        }
    }
}

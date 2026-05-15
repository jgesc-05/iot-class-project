<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    /**
     * POST /api/alert-rules
     *
     * Crea una regla de alerta nueva. Lo consume la UI Livewire.
     */
    public function storeRule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'device_id' => ['required', 'string', 'exists:devices,device_id'],
            'measurement' => ['required', 'string', 'max:64'],
            'min_threshold' => ['nullable', 'numeric'],
            'max_threshold' => ['nullable', 'numeric'],
        ]);

        // Al menos uno de los thresholds debe estar definido
        if (($validated['min_threshold'] ?? null) === null && ($validated['max_threshold'] ?? null) === null) {
            $this->jsonError(422, 'invalid_thresholds',
                'Debes definir al menos min_threshold o max_threshold.');
        }

        $device = Device::where('device_id', $validated['device_id'])->firstOrFail();

        $rule = AlertRule::create([
            'device_id' => $device->id,
            'name' => $validated['name'],
            'measurement' => $validated['measurement'],
            'min_threshold' => $validated['min_threshold'] ?? null,
            'max_threshold' => $validated['max_threshold'] ?? null,
            'enabled' => true,
        ]);

        return response()->json($this->formatRule($rule, $device), 201);
    }

    /**
     * GET /api/alert-rules
     *
     * Lista todas las reglas (activas e inactivas). La UI filtra.
     */
    public function listRules(): JsonResponse
    {
        $rules = AlertRule::with('device')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($rule) => $this->formatRule($rule, $rule->device));

        return response()->json(['rules' => $rules]);
    }

    /**
     * DELETE /api/alert-rules/{id}
     *
     * Soft delete: cambia enabled=false en lugar de borrar.
     * Idempotente: si ya estaba deshabilitada, devuelve 204 igual.
     */
    public function disableRule(int $id): Response
    {
        $rule = AlertRule::find($id);

        if (!$rule) {
            $this->jsonError(404, 'rule_not_found',
                "La regla con id {$id} no existe.");
        }

        $rule->update(['enabled' => false]);

        return response()->noContent();
    }

    /**
     * DELETE /api/alert-rules/{id}/destroy
     *
     * Elimina permanentemente una regla y sus alertas asociadas (cascade).
     */
    public function destroyRule(int $id): Response
    {
        $rule = AlertRule::find($id);

        if (!$rule) {
            $this->jsonError(404, 'rule_not_found',
                "La regla con id {$id} no existe.");
        }

        $rule->delete();

        return response()->noContent();
    }

    /**
     * PATCH /api/alert-rules/{id}/enable
     *
     * Reactiva una regla deshabilitada. Idempotente.
     */
    public function enableRule(int $id): Response
    {
        $rule = AlertRule::find($id);

        if (!$rule) {
            $this->jsonError(404, 'rule_not_found',
                "La regla con id {$id} no existe.");
        }

        $rule->update(['enabled' => true]);

        return response()->noContent();
    }

    /**
     * GET /api/alerts?status=pending|resolved|all&limit=50
     *
     * Lista las alertas filtradas por status.
     */
    public function listAlerts(Request $request): JsonResponse
    {
        $status = $request->query('status', 'pending');
        $limit = min((int) $request->query('limit', 50), 200);

        $query = Alert::with(['alertRule', 'device'])
            ->limit($limit);

        // En 'all' mostramos primero las pendientes, luego las resueltas
        if ($status === 'all') {
            $query->orderByRaw('resolved_at IS NOT NULL')
                  ->orderByDesc('triggered_at');
        } else {
            $query->orderByDesc('triggered_at');
        }

        if ($status === 'pending') {
            $query->whereNull('resolved_at');
        } elseif ($status === 'resolved') {
            $query->whereNotNull('resolved_at');
        }
        // 'all' no filtra

        $alerts = $query->get()->map(fn($alert) => [
            'id' => $alert->id,
            'rule_id' => $alert->alert_rule_id,
            'rule_name' => $alert->alertRule->name ?? 'Regla sin nombre',
            'device_id' => $alert->device->device_id,
            'measurement' => $alert->alertRule->measurement,
            'value' => $alert->value,
            'min_threshold' => $alert->alertRule->min_threshold,
            'max_threshold' => $alert->alertRule->max_threshold,
            'triggered_at' => $alert->triggered_at,
            'resolved_at' => $alert->resolved_at,
        ]);

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * PATCH /api/alerts/{id}/resolve
     *
     * Marca una alerta como resuelta. Idempotente.
     */
    public function resolveAlert(int $id): Response
    {
        $alert = Alert::find($id);

        if (!$alert) {
            $this->jsonError(404, 'alert_not_found',
                "La alerta con id {$id} no existe.");
        }

        // Si ya estaba resuelta, no la sobrescribimos (preservamos el resolved_at original)
        if ($alert->resolved_at === null) {
            $alert->resolve();
        }

        return response()->noContent();
    }

    /**
     * Formatea una regla para respuesta JSON con device_id como slug publico.
     */
    private function formatRule(AlertRule $rule, Device $device): array
    {
        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'device_id' => $device->device_id,
            'measurement' => $rule->measurement,
            'min_threshold' => $rule->min_threshold,
            'max_threshold' => $rule->max_threshold,
            'enabled' => $rule->enabled,
            'created_at' => $rule->created_at,
        ];
    }

    /**
     * Lanza una respuesta JSON limpia con el status code dado.
     * Mismo patron que CommandController para consistencia.
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

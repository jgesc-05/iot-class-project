<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use Illuminate\Http\JsonResponse;

class MetricController extends Controller
{
    /**
     * POST /api/metrics
     *
     * Recibe una métrica de un dispositivo. La validación del payload
     * se hace en StoreMetricRequest. La validación de la API key y la
     * persistencia vendrán en las tareas 3.3 y 3.4.
     */
    public function store(StoreMetricRequest $request): JsonResponse
    {
        // Si llegamos aquí, el payload ya pasó la validación.
        $data = $request->validated();

        return response()->json([
            'accepted' => true,
            'message' => 'payload válido — falta validar api_key y persistir',
            'received' => $data,
        ], 201);
    }
}

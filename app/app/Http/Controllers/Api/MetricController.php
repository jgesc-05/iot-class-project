<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MetricController extends Controller
{
    /**
     * POST /api/metrics
     *
     * Recibe una métrica de un dispositivo. Por ahora solo devuelve
     * lo que recibió, sin validar ni guardar. Eso vendrá en las
     * tareas 3.2, 3.3 y 3.4.
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'accepted' => true,
            'message' => 'endpoint placeholder — sin validación ni persistencia aún',
            'received' => $request->all(),
        ], 201);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMetricRequest extends FormRequest
{
    /**
     * Permitir todas las requests (la autorización por API key
     * se hace en una capa posterior, en la tarea 3.3).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación del payload.
     */
    public function rules(): array
    {
        return [
            'device_id'   => ['required', 'string', 'max:64', 'exists:devices,device_id'],
            'api_key'     => ['required', 'string'],
            'measurement' => ['required', 'string', 'max:64'],
            'value'       => ['required', 'numeric'],
            'unit'        => ['nullable', 'string', 'max:20'],
            'metadata'    => ['nullable', 'array'],
            'timestamp'   => ['nullable', 'date'],
        ];
    }

    /**
     * Mensajes personalizados (opcional pero ayuda a debugging).
     */
    public function messages(): array
    {
        return [
            'device_id.exists' => 'El device_id no existe en el sistema.',
            'value.numeric'    => 'El valor debe ser numérico.',
            'timestamp.date'   => 'El timestamp debe ser una fecha ISO válida.',
        ];
    }
}

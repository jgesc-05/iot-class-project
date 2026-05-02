<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DeviceDetail extends Component
{
    public $device;

    public function mount($deviceId)
    {
        $this->device = \App\Models\Device::findOrFail($deviceId);
    }

    public function render()
    {
        return view('livewire.device-detail')->layout('layouts.app');
    }

    public function sendCommand(string $type, array $payload)
    {
        // Consume el endpoint POST /api/commands para mantener consistencia
        // con el contrato de comandos (validacion centralizada, audit log, etc).
        $response = Http::post(url('/api/commands'), [
            'device_id' => $this->device->device_id,
            'type'      => $type,
            'payload'   => $payload,
        ]);

        if ($response->successful()) {
            session()->flash('ok', 'Comando enviado');
        } else {
            $error = $response->json('message') ?? 'Error desconocido';
            session()->flash('error', "No se pudo enviar el comando: {$error}");
        }
    }
}

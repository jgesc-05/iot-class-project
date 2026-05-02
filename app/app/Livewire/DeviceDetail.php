<?php

namespace App\Livewire;

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DeviceDetail extends Component
{
    public Device $device;
    public ?int $newInterval = null;

    public function mount($deviceId): void
    {
        $this->device = Device::findOrFail($deviceId);
    }

    /**
     * Trae la ultima metrica registrada para este dispositivo.
     * Devuelve null si aun no hay metricas.
     */
    public function getLatestMetricProperty()
    {
        return DB::table('metrics')
            ->where('device_id', $this->device->device_id)
            ->orderByDesc('time')
            ->first();
    }

    /**
     * Trae los ultimos 20 comandos enviados a este dispositivo.
     */
    public function getRecentCommandsProperty()
    {
        return DB::table('commands')
            ->where('device_id', $this->device->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.device-detail')->layout('layouts.app');
    }

    public function sendCommand(string $type, array $payload)
    {
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

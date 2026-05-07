<?php

namespace App\Livewire;

use App\Models\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;

class DeviceDetail extends Component
{
    use WithPagination;

    public function paginationView()
    {
        return 'vendor.pagination.tailwind';
    }

    public Device $device;

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

    public function render()
    {
        $commands = Command::where('device_id', $this->device->id)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withPath(route('devices.show', $this->device->id));

        return view('livewire.device-detail', compact('commands'))
            ->layout('layouts.app');
    }

    public function sendCommand(string $type, array $payload = [])
    {
        // Usamos hostname interno de Docker (web = nginx). url() devolveria
        // localhost:8000 que no es accesible desde el contenedor app (php-fpm).
        $baseUrl = env('INTERNAL_API_URL', 'http://web');
        $response = Http::post($baseUrl . '/api/commands', [
            'device_id' => $this->device->device_id,
            'type'      => $type,
            'payload'   => $payload,
        ]);


        if ($response->successful()) {
            if ($type === 'on_off') {
                $this->device->update([
                    'status' => ($payload['on'] ?? false) ? 'active' : 'inactive',
                ]);
                $this->device->refresh();
            }
            session()->flash('ok', 'Comando enviado');
        } else {
            $error = $response->json('message') ?? 'Error desconocido';
            session()->flash('error', "No se pudo enviar el comando: {$error}");
        }
    }
}

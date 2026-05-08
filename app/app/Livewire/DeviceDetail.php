<?php

namespace App\Livewire;

use App\Livewire\DeviceCreate;
use App\Models\Command;
use App\Models\Device;
use App\Services\SimulatorService;
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
    public bool $simulating = false;

    // Campos editables
    public bool $editing = false;
    public string $editName = '';
    public string $editUnit = '';
    public string $editCustomUnit = '';
    public $editRangeMin = null;
    public $editRangeMax = null;
    public string $editDescription = '';

    public function mount($deviceId): void
    {
        $this->device = Device::findOrFail($deviceId);
        $this->simulating = SimulatorService::isSimulating($this->device->device_id);
    }

    // Unidades disponibles segun la medicion del dispositivo
    public function getAvailableUnitsProperty(): array
    {
        return DeviceCreate::MEASUREMENT_UNITS[$this->device->measurement] ?? [];
    }

    public function openEdit(): void
    {
        $this->editName = $this->device->name;
        $units = $this->availableUnits;
        if (in_array($this->device->unit, $units)) {
            $this->editUnit = $this->device->unit;
            $this->editCustomUnit = '';
        } elseif (!empty($units)) {
            // La unidad actual no esta en las opciones predefinidas
            $this->editUnit = '__custom__';
            $this->editCustomUnit = $this->device->unit;
        } else {
            // Medicion sin unidades predefinidas (personalizada)
            $this->editUnit = '';
            $this->editCustomUnit = $this->device->unit;
        }
        $metadata = $this->device->metadata ?? [];
        $this->editRangeMin = $metadata['sim_min'] ?? null;
        $this->editRangeMax = $metadata['sim_max'] ?? null;
        $this->editDescription = $metadata['description'] ?? '';
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $finalUnit = $this->editUnit === '__custom__' || $this->editUnit === ''
            ? $this->editCustomUnit
            : $this->editUnit;

        $this->validate([
            'editName'        => 'required|string|max:100',
            'editRangeMin'    => 'nullable|numeric',
            'editRangeMax'    => 'nullable|numeric',
            'editDescription' => 'nullable|string|max:255',
        ]);

        if (empty($finalUnit)) {
            $this->addError('editUnit', 'La unidad es obligatoria.');
            return;
        }

        if ($this->editRangeMin !== null && $this->editRangeMin !== '' &&
            $this->editRangeMax !== null && $this->editRangeMax !== '' &&
            (float) $this->editRangeMax <= (float) $this->editRangeMin) {
            $this->addError('editRangeMax', 'El rango maximo debe ser mayor que el minimo.');
            return;
        }

        $metadata = $this->device->metadata ?? [];

        // Actualizar rangos en metadata
        if ($this->editRangeMin !== null && $this->editRangeMin !== '') {
            $metadata['sim_min'] = (float) $this->editRangeMin;
        } else {
            unset($metadata['sim_min']);
        }

        if ($this->editRangeMax !== null && $this->editRangeMax !== '') {
            $metadata['sim_max'] = (float) $this->editRangeMax;
        } else {
            unset($metadata['sim_max']);
        }

        if (!empty($this->editDescription)) {
            $metadata['description'] = $this->editDescription;
        } else {
            unset($metadata['description']);
        }

        $this->device->update([
            'name'     => $this->editName,
            'unit'     => $finalUnit,
            'metadata' => !empty($metadata) ? $metadata : null,
        ]);

        $this->device->refresh();
        $this->editing = false;
        session()->flash('ok', 'Dispositivo actualizado');
    }

    public function startSimulation(): void
    {
        $this->simulating = true;
        SimulatorService::start($this->device->device_id);
    }

    public function stopSimulation(): void
    {
        $this->simulating = false;
        SimulatorService::stop($this->device->device_id);
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

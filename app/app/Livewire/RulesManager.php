<?php

namespace App\Livewire;

use App\Models\AlertRule;
use App\Models\Device;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

class RulesManager extends Component
{
    use WithPagination;

    public $name = '';
    public $device_id = '';
    public $measurement = '';
    public $min_threshold = null;
    public $max_threshold = null;
    public $devices = [];

    public function paginationView()
    {
        return 'vendor.pagination.tailwind';
    }

    public function mount()
    {
        $this->devices = Device::orderBy('name')
            ->get(['id', 'name', 'device_id', 'measurement']);
    }

    public function updatedDeviceId()
    {
        $device = collect($this->devices)->firstWhere('device_id', $this->device_id);
        $this->measurement = $device['measurement'] ?? '';
    }

    public function save()
    {
        $this->validate([
            'name'          => 'required|string|max:200',
            'device_id'     => 'required|string',
            'measurement'   => 'required|string|max:64',
            'min_threshold' => 'nullable|numeric',
            'max_threshold' => 'nullable|numeric',
        ]);

        if ($this->min_threshold === null && $this->max_threshold === null) {
            session()->flash('error', 'Debes definir al menos un umbral (minimo o maximo).');
            return;
        }

        $response = Http::post(env('INTERNAL_API_URL') . '/api/alert-rules', [
            'name'          => $this->name,
            'device_id'     => $this->device_id,
            'measurement'   => $this->measurement,
            'min_threshold' => $this->min_threshold,
            'max_threshold' => $this->max_threshold,
            'enabled'       => true,
        ]);

        if ($response->successful()) {
            session()->flash('ok', 'Regla creada');
            $this->reset(['name', 'device_id', 'measurement', 'min_threshold', 'max_threshold']);
        } else {
            session()->flash('error', 'Error al crear la regla');
        }
    }

    public function disable($id)
    {
        $response = Http::delete(env('INTERNAL_API_URL') . "/api/alert-rules/{$id}");

        if ($response->successful()) {
            session()->flash('ok', 'Regla desactivada');
        } else {
            session()->flash('error', 'Error al desactivar');
        }
    }

    public function enable($id)
    {
        $response = Http::patch(env('INTERNAL_API_URL') . "/api/alert-rules/{$id}/enable");

        if ($response->successful()) {
            session()->flash('ok', 'Regla activada');
        } else {
            session()->flash('error', 'Error al activar');
        }
    }

    public function render()
    {
        $rules = AlertRule::with('device')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.rules-manager', compact('rules'))
            ->layout('layouts.app');
    }
}

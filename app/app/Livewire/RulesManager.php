<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class RulesManager extends Component
{
    public $name = '';
    public $device_id = '';
    public $measurement = '';
    public $min_threshold = null;
    public $max_threshold = null;
    public $devices = [];
    public $measurements = [];

    public function mount()
    {
        $this->devices = Device::where('user_id', auth()->id())
            ->get(['id', 'name', 'device_id', 'measurement']);

        $this->measurements = Device::where('user_id', auth()->id())
            ->distinct()->pluck('measurement');
    }

    public function save()
    {
        $this->validate([
            'name'      => 'required',
            'device_id' => 'required|exists:devices,id',
        ]);

        \App\Models\AlertRule::create([
            'name'          => $this->name,
            'device_id'     => $this->device_id,
            'measurement'   => $this->measurement,
            'min_threshold' => $this->min_threshold,
            'max_threshold' => $this->max_threshold,
            'enabled'       => true,
        ]);

        session()->flash('ok', 'Regla creada');
        $this->reset(['name', 'device_id', 'measurement', 'min_threshold', 'max_threshold']);
    }

    public function disable($id)
    {
        \App\Models\AlertRule::findOrFail($id)->update(['enabled' => false]);
    }


    public function render()
    {
        $rules = \App\Models\AlertRule::latest()->get()->map(fn($r) => [
            'id'            => $r->id,
            'name'          => $r->name,
            'device_id' => $r->device->name ?? '—',
            'measurement'   => $r->measurement,
            'min_threshold' => $r->min_threshold,
            'max_threshold' => $r->max_threshold,
            'enabled'       => $r->enabled,
        ])->toArray();

        return view('livewire.rules-manager', compact('rules'))
            ->layout('layouts.app');
    }
}

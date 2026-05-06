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
            'name'        => 'required',
            'device_id'   => 'required',
            'measurement' => 'required',
        ]);

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



    public function render()
    {
        $response = Http::get(env('INTERNAL_API_URL') . '/api/alert-rules');

        $rules = $response->successful() ? $response->json('rules') : [];

        return view('livewire.rules-manager', compact('rules'))
            ->layout('layouts.app');
    }
}

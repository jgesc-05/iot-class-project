<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;

class DeviceCreate extends Component
{
    public $name = '';
    public $unit = '';
    public $measurement = '';
    public $type = '';
    public $sample_interval = 0;

    public $measurements = [];
    public $types = [];

    //Mandar datos al formulario
    public function mount()
    {
        $this->measurements = Device::where('user_id', auth()->id())
            ->distinct()->pluck('measurement');

        $this->types = Device::where('user_id', auth()->id())
            ->distinct()->pluck('type');
    }

    //Crear el dispositivo
    public function save() {
        $plainKey = 'dk_' . bin2hex(random_bytes(16));
        $device = Device::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'device_id' => 'dev-' . uniqid(),
            'type' => $this->type,
            'measurement' => $this->measurement,
            'unit' => $this->unit,
            'api_key_hash' => hash('sha256', $plainKey),
            'sample_interval_s' => $this->sample_interval,
        ]);
        session()->flash('new_api_key', $plainKey);
        return redirect()->route('devices.show', $device);
    }


    public function render()
    {
        return view('livewire.device-create')->layout('layouts.app');
    }
}

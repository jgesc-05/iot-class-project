<?php

namespace App\Livewire;

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
        \App\Models\Command::create([
            'device_id' => $this->device->id,
            'type'      => $type,
            'payload'   => $payload,
        ]);
        session()->flash('ok', 'Comando enviado');
    }
}

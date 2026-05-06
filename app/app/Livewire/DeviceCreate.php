<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;

class DeviceCreate extends Component
{
    public $name = '';
    public $unit = '';
    public $customUnit = '';
    public $measurement = '';
    public $type = '';
    public $sample_interval = 0;

    public array $measurements = [
        'temperature',
        'humidity',
        'soil_moisture',
        'light',
        'co2',
        'ph',
        'pressure',
    ];

    public array $types = [
        'real',
        'twin',
        'api',
        'dataset',
    ];

    public array $units = [
        '°C',
        '°F',
        '%',
        'ppm',
        'lux',
        'hPa',
        'pH',
        'V',
        'mA',
    ];

    public function save()
    {
        $finalUnit = $this->unit === '__custom__' ? $this->customUnit : $this->unit;
        $this->unit = $finalUnit;

        $this->validate([
            'name'            => 'required|string|max:100',
            'type'            => 'required|in:real,twin,api,dataset',
            'measurement'     => 'required|string|max:64',
            'unit'            => 'required|string|max:20',
            'sample_interval' => 'required|integer|min:1',
        ]);

        $plainKey = 'dk_' . bin2hex(random_bytes(16));
        $device = Device::create([
            'user_id'           => auth()->id(),
            'name'              => $this->name,
            'device_id'         => 'dev-' . uniqid(),
            'type'              => $this->type,
            'measurement'       => $this->measurement,
            'unit'              => $this->unit,
            'api_key_hash'      => hash('sha256', $plainKey),
            'sample_interval_s' => $this->sample_interval,
        ]);

        session()->flash('new_api_key', $plainKey);
        session()->flash('ok', 'Dispositivo creado correctamente');
        return redirect()->route('devices.show', $device);
    }


    public function render()
    {
        return view('livewire.device-create')
            ->layout('layouts.app');
    }
}

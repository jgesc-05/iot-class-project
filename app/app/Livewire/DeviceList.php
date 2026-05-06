<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;

class DeviceList extends Component
{
    public function render()
    {
        $devices = Device::all()
            ->map(fn($d) => [
                'device' => $d,
                'last' => \DB::table('metrics')
                ->where('device_id', $d->device_id)
                ->orderByDesc('time')
                ->first(),
            ]);
        return view('livewire.device-list', compact('devices'))
            ->layout('layouts.app');
    }
}

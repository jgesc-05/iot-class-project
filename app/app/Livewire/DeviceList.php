<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;

class DeviceList extends Component
{
    public string $status = '';

    public function mount()
    {
        $this->status = request()->query('status', '');
    }

    public function render()
    {
        $query = Device::query();

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $devices = $query->get()
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

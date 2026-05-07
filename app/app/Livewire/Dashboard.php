<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Models\Device;
use Livewire\Component;

// Pagina principal /dashboard con resumen general del invernadero
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'totalDevices'  => Device::count(),
            'activeDevices' => Device::where('status', 'active')->count(),
            'inactiveDevices' => Device::where('status', 'inactive')->count(),
            'pendingAlerts' => Alert::whereNull('resolved_at')->count(),
        ])->layout('layouts.app');
    }
}

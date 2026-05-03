<?php
namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class AlertList extends Component
{
    public function resolve($id)
    {
        Http::patch(route('alerts.resolve', $id));
    }

    public function render()
    {
        $alerts = Alert::with(['alertRule', 'device'])
            ->whereNull('resolved_at')
            ->orderByDesc('triggered_at')
            ->get();

        return view('livewire.alert-list', compact('alerts'))
            ->layout('layouts.app');
    }
}

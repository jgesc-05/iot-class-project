<?php
namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class AlertList extends Component
{
    public function resolve($id)
    {
        $response = Http::patch(env('INTERNAL_API_URL') . "/api/alerts/{$id}/resolve");

        if ($response->successful())
        {
            session()->flash('ok', 'Alerta resuelta');
        }
        else
        {
            session()->flash('error', 'Error al resolver la alerta');
        }
    }

    public function render()
    {
        $response = Http::get(env('INTERNAL_API_URL') . "/api/alerts");

        $alerts = $response->successful() ? $response->json('alerts') : [];

        return view('livewire.alert-list', compact('alerts'))
            ->layout('layouts.app');
    }
}

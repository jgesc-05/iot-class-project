<?php
namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

class AlertList extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public function paginationView()
    {
        return 'vendor.pagination.tailwind';
    }

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

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
        $query = Alert::with(['alertRule', 'device']);

        if ($this->filter === 'pending') {
            $query->whereNull('resolved_at');
        } elseif ($this->filter === 'resolved') {
            $query->whereNotNull('resolved_at');
        } else {
            // 'all': pendientes primero
            $query->orderByRaw('resolved_at IS NOT NULL');
        }

        $alerts = $query->orderByDesc('triggered_at')->paginate(20);

        return view('livewire.alert-list', compact('alerts'))
            ->layout('layouts.app');
    }
}

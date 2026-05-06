<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $url = config('services.grafana.url');
        $uid = config('services.grafana.uid');

            $baseUrl = rtrim($url, '/');
            $iframeUrl = "{$baseUrl}/d-solo/{$uid}?orgId=1&panelId=1";

        return view('livewire.dashboard', compact('iframeUrl'))
            ->layout('layouts.app');
    }
}

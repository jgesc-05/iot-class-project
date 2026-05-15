<?php

namespace App\Livewire;

use Livewire\Component;

// Pagina con los 3 dashboards de Grafana embebidos via iframe.
// Cada tab carga un dashboard diferente.
class Dashboards extends Component
{
    public string $active = 'overview';

    // Definicion de dashboards disponibles (uid tomado de los JSON provisionados)
    public function getDashboards(): array
    {
        return [
            'overview' => [
                'label' => 'Vista General',
                'uid'   => 'overview',
            ],
            'calidad-rosa' => [
                'label' => 'Calidad de la Rosa',
                'uid'   => 'calidad-rosa',
            ],
            'alertas' => [
                'label' => 'Alertas y Anomalias',
                'uid'   => 'alertas',
            ],
        ];
    }

    public function setActive(string $key): void
    {
        $dashboards = $this->getDashboards();
        if (array_key_exists($key, $dashboards)) {
            $this->active = $key;
        }
    }

    public function render()
    {
        $dashboards = $this->getDashboards();
        $current = $dashboards[$this->active];
        $grafanaUrl = rtrim(config('services.grafana.url', 'http://localhost:3000'), '/');
        $iframeUrl = "{$grafanaUrl}/d/{$current['uid']}?orgId=1&kiosk=1&theme=light";

        return view('livewire.dashboards', [
            'dashboards' => $dashboards,
            'iframeUrl'  => $iframeUrl,
        ])->layout('layouts.app');
    }
}

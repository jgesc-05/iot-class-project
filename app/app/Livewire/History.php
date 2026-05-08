<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public function paginationView()
    {
        return 'vendor.pagination.tailwind';
    }

    /**
     * Filtros expuestos como query params en la URL para que la vista
     * sea bookmarkeable/compartible. Ejemplo:
     *   /history?device=sensor-temp-a&from=2026-04-29&to=2026-05-03
     */
    #[Url(as: 'device')]
    public ?string $deviceId = null;

    #[Url(as: 'from')]
    public ?string $fromDate = null;

    #[Url(as: 'to')]
    public ?string $toDate = null;

    /**
     * Unidad del dispositivo seleccionado. Se actualiza automaticamente
     * en updatedDeviceId(). Como propiedad publica, es accesible desde
     * Alpine via $wire.unit (las computed properties NO lo son).
     */
    public string $unit = '';

    public function mount(): void
    {
        if (!$this->fromDate) {
            $this->fromDate = now()->subDay()->format('Y-m-d\TH:i');
        }
        if (!$this->toDate) {
            $this->toDate = now()->format('Y-m-d\TH:i');
        }
        if ($this->deviceId) {
            $device = Device::where('device_id', $this->deviceId)->first();
            $this->unit = $device?->unit ?? '';
        }
    }

    /**
     * Lista de dispositivos para el dropdown del form.
     * Incluye todos (activos e inactivos) para poder ver historial de cualquiera.
     */
    public function getDevicesProperty()
    {
        return Device::orderBy('name')
            ->get(['id', 'device_id', 'name', 'measurement', 'unit', 'status']);
    }

    /**
     * Dispositivo actualmente seleccionado (objeto completo) para mostrar
     * informacion de unidad/measurement en la UI.
     */
    public function getSelectedDeviceProperty()
    {
        if (!$this->deviceId) {
            return null;
        }
        return $this->devices->firstWhere('device_id', $this->deviceId);
    }

    /**
     * Hook de Livewire: se ejecuta cada vez que cambia $deviceId.
     * Actualizamos $unit aqui para que el JS pueda leerla via $wire.unit.
     */
    public function updatedDeviceId(): void
    {
        $device = $this->selectedDevice;
        $this->unit = $device?->unit ?? '';
    }

    /**
     * Hook de Livewire: se ejecuta al montar. Si ya hay deviceId en la URL,
     * inicializamos $unit aqui (updatedDeviceId no se dispara en mount).
     */
    public function bootedDeviceId(): void
    {
        // No-op, lo manejamos en mount.
    }

    /**
     * Setea el rango temporal a las ultimas N horas (botones de rango rapido).
     */
    public function setRange(int $hours): void
    {
        $this->fromDate = now()->subHours($hours)->format('Y-m-d\\TH:i');
        $this->toDate = now()->format('Y-m-d\\TH:i');
    }

    /**
     * Metricas paginadas en el rango seleccionado. 50 por pagina.
     * Se evalua en cada render automaticamente cuando cambian filtros.
     */
    public function getMetricsProperty()
    {
        if (!$this->deviceId || !$this->fromDate || !$this->toDate) {
            return null;
        }

        try {
            $from = \Carbon\Carbon::parse($this->fromDate, config('app.timezone'))->utc();
            $to = \Carbon\Carbon::parse($this->toDate, config('app.timezone'))->utc();
        } catch (\Exception $e) {
            return null;
        }

        return \Illuminate\Support\Facades\DB::table('metrics')
            ->where('device_id', $this->deviceId)
            ->whereBetween('time', [$from, $to])
            ->orderByDesc('time')
            ->paginate(50)
            ->withQueryString();
    }

    public function render()
    {
        // Si toDate esta cerca de "ahora" (menos de 5 min), lo extendemos
        // automaticamente para que los datos nuevos aparezcan en el rango.
        try {
            $to = \Carbon\Carbon::parse($this->toDate, config('app.timezone'));
            if ($to->diffInMinutes(now()) < 5) {
                $this->toDate = now()->format('Y-m-d\TH:i');
            }
        } catch (\Exception $e) {
            // ignorar
        }

        return view('livewire.history')->layout('layouts.app');
    }
}

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
    public $customMeasurement = '';
    public $type = '';
    public $description = '';
    public $range_min = null;
    public $range_max = null;

    // Mapa de mediciones con sus unidades compatibles.
    // Las claves son los valores que se guardan en BD.
    public const MEASUREMENT_UNITS = [
        'temperatura_ambiente'  => ['°C', '°F', 'K'],
        'humedad_ambiente'      => ['%'],
        'co2'                   => ['ppm'],
        'luminosidad'           => ['lux', 'fc'],
        'humedad_suelo'         => ['V', '%'],
        'temperatura_suelo'     => ['°C', '°F'],
        'ph_agua'               => ['pH'],
        'corriente_extractor'   => ['A', 'mA'],
        'color_boton'           => ['nm'],
        'temperatura_foliar'    => ['°C', '°F'],
        'altura_tallo'          => ['mm', 'cm'],
        'ec'                    => ['mS/cm', 'dS/m'],
        'presion_atmosferica'   => ['hPa', 'mmHg'],
        'voltaje'               => ['V', 'mV'],
    ];

    // Etiquetas legibles para cada medicion
    public const MEASUREMENT_LABELS = [
        'temperatura_ambiente'  => 'Temperatura ambiente',
        'humedad_ambiente'      => 'Humedad ambiente',
        'co2'                   => 'Dioxido de carbono (CO2)',
        'luminosidad'           => 'Intensidad luminica',
        'humedad_suelo'         => 'Humedad del suelo',
        'temperatura_suelo'     => 'Temperatura suelo / agua',
        'ph_agua'               => 'pH del agua',
        'corriente_extractor'   => 'Corriente de extractor',
        'color_boton'           => 'Color del boton floral',
        'temperatura_foliar'    => 'Temperatura foliar',
        'altura_tallo'          => 'Altura del tallo',
        'ec'                    => 'Conductividad electrica (EC)',
        'presion_atmosferica'   => 'Presion atmosferica',
        'voltaje'               => 'Voltaje',
    ];

    public array $types = [
        'real',
        'twin',
        'api',
        'dataset',
    ];

    // Cuando cambia la medicion, resetear unidad para que el usuario elija una valida
    public function updatedMeasurement(): void
    {
        $this->unit = '';
        $this->customUnit = '';
    }

    // Unidades disponibles segun la medicion seleccionada
    public function getAvailableUnitsProperty(): array
    {
        if ($this->measurement === '__custom__' || $this->measurement === '') {
            return [];
        }
        return self::MEASUREMENT_UNITS[$this->measurement] ?? [];
    }

    // Determina si la medicion es personalizada
    public function getIsCustomMeasurementProperty(): bool
    {
        return $this->measurement === '__custom__';
    }

    public function save()
    {
        $finalMeasurement = $this->measurement === '__custom__'
            ? $this->customMeasurement
            : $this->measurement;

        $finalUnit = $this->measurement === '__custom__'
            ? $this->customUnit
            : ($this->unit === '__custom__' ? $this->customUnit : $this->unit);

        // Reemplazar temporalmente para validacion
        $originalMeasurement = $this->measurement;
        $originalUnit = $this->unit;
        $this->measurement = $finalMeasurement;
        $this->unit = $finalUnit;

        $this->validate([
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:real,twin,api,dataset',
            'measurement' => 'required|string|max:64',
            'unit'        => 'required|string|max:20',
            'range_min'   => 'nullable|numeric',
            'range_max'   => 'nullable|numeric',
            'description' => 'nullable|string|max:255',
        ]);

        // Validar que range_max > range_min si ambos estan definidos
        if ($this->range_min !== null && $this->range_min !== '' &&
            $this->range_max !== null && $this->range_max !== '' &&
            (float) $this->range_max <= (float) $this->range_min) {
            $this->measurement = $originalMeasurement;
            $this->unit = $originalUnit;
            $this->addError('range_max', 'El rango maximo debe ser mayor que el minimo.');
            return;
        }

        $metadata = [];

        if ($this->range_min !== null && $this->range_min !== '') {
            $metadata['sim_min'] = (float) $this->range_min;
        }
        if ($this->range_max !== null && $this->range_max !== '') {
            $metadata['sim_max'] = (float) $this->range_max;
        }
        if (!empty($this->description)) {
            $metadata['description'] = $this->description;
        }

        $plainKey = 'dk_' . bin2hex(random_bytes(16));
        $device = Device::create([
            'user_id'           => auth()->id(),
            'name'              => $this->name,
            'device_id'         => 'dev-' . uniqid(),
            'type'              => $this->type,
            'measurement'       => $this->measurement,
            'unit'              => $this->unit,
            'api_key_hash'      => hash('sha256', $plainKey),
            'sample_interval_s' => 15,
            'metadata'          => !empty($metadata) ? $metadata : null,
        ]);

        session()->flash('new_api_key', $plainKey);
        session()->flash('ok', 'Dispositivo creado correctamente');
        return redirect()->route('devices.show', $device);
    }

    public function render()
    {
        return view('livewire.device-create', [
            'measurementUnits'  => self::MEASUREMENT_UNITS,
            'measurementLabels' => self::MEASUREMENT_LABELS,
        ])->layout('layouts.app');
    }
}

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
        'temperatura_exterior'  => ['°C', '°F', 'K'],
        'humedad_ambiente'      => ['%'],
        'humedad_exterior'      => ['%'],
        'humedad_sustrato'      => ['%'],
        'co2'                   => ['ppm'],
        'luminosidad'           => ['lux', 'fc'],
        'ph_sustrato'           => ['pH'],
        'ec'                    => ['mS/cm', 'dS/m'],
        'presion_atmosferica'   => ['hPa', 'mmHg', 'atm'],
        'velocidad_viento'      => ['m/s', 'km/h'],
        'nivel_agua'            => ['cm', 'L'],
        'voltaje'               => ['V', 'mV'],
        'corriente'             => ['A', 'mA'],
    ];

    // Etiquetas legibles para cada medicion
    public const MEASUREMENT_LABELS = [
        'temperatura_ambiente'  => 'Temperatura ambiente',
        'temperatura_exterior'  => 'Temperatura exterior',
        'humedad_ambiente'      => 'Humedad ambiente',
        'humedad_exterior'      => 'Humedad exterior',
        'humedad_sustrato'      => 'Humedad del sustrato',
        'co2'                   => 'CO2',
        'luminosidad'           => 'Luminosidad',
        'ph_sustrato'           => 'pH del sustrato',
        'ec'                    => 'Conductividad electrica (EC)',
        'presion_atmosferica'   => 'Presion atmosferica',
        'velocidad_viento'      => 'Velocidad del viento',
        'nivel_agua'            => 'Nivel de agua',
        'voltaje'               => 'Voltaje',
        'corriente'             => 'Corriente electrica',
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

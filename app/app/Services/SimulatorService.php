<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Servicio de simulacion de datos IoT.
// Replica la logica de generacion de valores de simulators/base.py y simulators/profiles.py
// para poder generar metricas directamente desde la app web.
// Gestiona el estado de simulacion en cache y la insercion de metricas.

class SimulatorService
{
    // Perfiles de simulacion por tipo de medicion.
    // Base y amplitude definen la oscilacion sinusoidal, noise el ruido aleatorio.
    // Los rangos de contexto (Bucaramanga) se aplican via sim_min/sim_max en metadata.
    public const PROFILES = [
        'temperatura_ambiente'  => ['base' => 27.0,   'amplitude' => 3.0,     'noise' => 0.3],
        'humedad_ambiente'      => ['base' => 75.0,   'amplitude' => 8.0,     'noise' => 1.5],
        'co2'                   => ['base' => 650.0,  'amplitude' => 150.0,   'noise' => 30],
        'luminosidad'           => ['base' => 37000,  'amplitude' => 15000,   'noise' => 800],
        'humedad_suelo'         => ['base' => 1.9,    'amplitude' => 0.2,     'noise' => 0.03],
        'temperatura_suelo'     => ['base' => 24.0,   'amplitude' => 2.0,     'noise' => 0.2],
        'ph_agua'               => ['base' => 6.7,    'amplitude' => 0.4,     'noise' => 0.05],
        'corriente_extractor'   => ['base' => 6.5,    'amplitude' => 2.5,     'noise' => 0.3],
        'color_boton'           => ['base' => 550.0,  'amplitude' => 60.0,    'noise' => 10],
        'temperatura_foliar'    => ['base' => 29.0,   'amplitude' => 3.0,     'noise' => 0.3],
        'altura_tallo'          => ['base' => 600.0,  'amplitude' => 300.0,   'noise' => 15],
    ];

    // Perfil generico para dispositivos cuyo measurement no tiene perfil especifico.
    // Oscila entre ~15 y ~35 con ruido moderado.
    public const DEFAULT_PROFILE = ['base' => 25.0, 'amplitude' => 10.0, 'noise' => 1.0];

    private const CACHE_KEY = 'simulating_devices';

    // -- Gestion de estado de simulacion --

    // Marca un dispositivo como simulando.
    public static function start(string $deviceId): void
    {
        $devices = Cache::get(self::CACHE_KEY, []);
        $devices[$deviceId] = true;
        Cache::put(self::CACHE_KEY, $devices);
    }

    // Detiene la simulacion de un dispositivo.
    public static function stop(string $deviceId): void
    {
        $devices = Cache::get(self::CACHE_KEY, []);
        unset($devices[$deviceId]);
        Cache::put(self::CACHE_KEY, $devices);
    }

    // Verifica si un dispositivo esta simulando.
    public static function isSimulating(string $deviceId): bool
    {
        $devices = Cache::get(self::CACHE_KEY, []);
        return isset($devices[$deviceId]);
    }

    // Retorna los device_ids de todos los dispositivos simulando.
    public static function getSimulatingDeviceIds(): array
    {
        return array_keys(Cache::get(self::CACHE_KEY, []));
    }

    // -- Generacion de valores --

    // Genera un valor simulado.
    // Si hay rangos (min/max), genera valores oscilando dentro de ese rango.
    // Si no, usa el perfil predefinido o el generico.
    public static function generate(string $measurement, ?float $min = null, ?float $max = null): float
    {
        if ($min !== null && $max !== null) {
            // Rangos personalizados: oscila dentro del rango completo
            $center = ($min + $max) / 2;
            $halfRange = ($max - $min) / 2;
            $oscillation = $halfRange * 0.6 * sin(time() / 60);
            $noise = $halfRange * 0.15 * (mt_rand() / mt_getrandmax() * 2 - 1);
            $value = $center + $oscillation + $noise;
            return round(max($min, min($max, $value)), 2);
        }

        if ($min !== null) {
            // Solo minimo: usa perfil pero clampea por abajo
            $profile = self::PROFILES[$measurement] ?? self::DEFAULT_PROFILE;
            $value = $profile['base'] + $profile['amplitude'] * sin(time() / 60)
                   + $profile['noise'] * (mt_rand() / mt_getrandmax() * 2 - 1);
            return round(max($min, $value), 2);
        }

        if ($max !== null) {
            // Solo maximo: usa perfil pero clampea por arriba
            $profile = self::PROFILES[$measurement] ?? self::DEFAULT_PROFILE;
            $value = $profile['base'] + $profile['amplitude'] * sin(time() / 60)
                   + $profile['noise'] * (mt_rand() / mt_getrandmax() * 2 - 1);
            return round(min($max, $value), 2);
        }

        // Sin rangos: perfil predefinido o generico
        $profile = self::PROFILES[$measurement] ?? self::DEFAULT_PROFILE;
        $oscillation = $profile['amplitude'] * sin(time() / 60);
        $noise = $profile['noise'] * (mt_rand() / mt_getrandmax() * 2 - 1);
        return round($profile['base'] + $oscillation + $noise, 2);
    }

    // Genera e inserta una metrica simulada para un dispositivo.
    // Si el dispositivo tiene sim_min/sim_max en metadata (definidos al crearlo),
    // el valor generado se limita a ese rango.
    public static function tick(Device $device): void
    {
        $metadata = $device->metadata ?? [];
        $min = $metadata['sim_min'] ?? null;
        $max = $metadata['sim_max'] ?? null;

        $value = self::generate($device->measurement, $min, $max);

        DB::table('metrics')->insert([
            'time'      => now('UTC'),
            'device_id' => $device->device_id,
            'value'     => $value,
            'metadata'  => json_encode(['source' => 'web_simulator']),
        ]);
    }
}

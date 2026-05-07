<?php

namespace App\Services;

// Servicio de simulacion de datos IoT.
// Replica la logica de generacion de valores de simulators/base.py y simulators/profiles.py
// para poder generar metricas directamente desde la app web.

class SimulatorService
{
    // Perfiles de simulacion por tipo de medicion.
    // Replica exacta de simulators/profiles.py
    public const PROFILES = [
        'temperatura_ambiente' => ['base' => 21.0, 'amplitude' => 4.0,   'noise' => 0.3],
        'humedad_ambiente'     => ['base' => 67.0, 'amplitude' => 8.0,   'noise' => 1.5],
        'humedad_sustrato'     => ['base' => 55.0, 'amplitude' => 5.0,   'noise' => 2.0],
        'co2'                  => ['base' => 700,  'amplitude' => 200,   'noise' => 40],
        'luminosidad'          => ['base' => 30000,'amplitude' => 25000, 'noise' => 1000],
        'ph_sustrato'          => ['base' => 6.0,  'amplitude' => 0.2,   'noise' => 0.05],
        'ec'                   => ['base' => 2.0,  'amplitude' => 0.3,   'noise' => 0.05],
    ];

    // Perfil generico para dispositivos cuyo measurement no tiene perfil especifico.
    // Oscila entre ~15 y ~35 con ruido moderado.
    public const DEFAULT_PROFILE = ['base' => 25.0, 'amplitude' => 10.0, 'noise' => 1.0];

    // Genera un valor simulado usando la misma formula que Python:
    // value = base + amplitude * sin(time / 60) + ruido_aleatorio
    // Si no hay perfil especifico, usa el perfil generico.
    public static function generate(string $measurement): float
    {
        $profile = self::PROFILES[$measurement] ?? self::DEFAULT_PROFILE;

        $oscillation = $profile['amplitude'] * sin(time() / 60);
        $noise = $profile['noise'] * (mt_rand() / mt_getrandmax() * 2 - 1);

        return round($profile['base'] + $oscillation + $noise, 2);
    }
}

<?php

namespace Database\Seeders;

use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@iot.local'],
            [
                'name' => 'Operador Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Dispositivos del invernadero de rosas Freedom/Explorer para exportacion.
        // Distribucion segun plano: pasillo central, cama A (norte), cama B (sur),
        // 4 extractores en esquinas, sensores foliares en pasillos norte/sur.
        $devices = [
            // --- Pasillo central ---
            ['name' => 'DHT22 Pasillo Central (Temp)',  'device_id' => 'sensor-temp-amb',     'type' => 'real', 'measurement' => 'temperatura_ambiente', 'unit' => '°C',  'min' => 22,    'max' => 32,    'rule_name' => 'Temp ambiente fuera de rango',        'meta' => ['zona' => 'pasillo-central', 'modelo' => 'DHT22']],
            ['name' => 'DHT22 Pasillo Central (Hum)',   'device_id' => 'sensor-hum-amb',      'type' => 'real', 'measurement' => 'humedad_ambiente',     'unit' => '%',   'min' => 65,    'max' => 85,    'rule_name' => 'Humedad ambiente fuera de rango',     'meta' => ['zona' => 'pasillo-central', 'modelo' => 'DHT22']],
            ['name' => 'MH-Z19C CO2',                   'device_id' => 'sensor-co2',          'type' => 'real', 'measurement' => 'co2',                  'unit' => 'ppm', 'min' => 400,   'max' => 900,   'rule_name' => 'CO2 fuera de rango',                  'meta' => ['zona' => 'pasillo-central', 'modelo' => 'MH-Z19C']],
            ['name' => 'BH1750 Luminosidad',             'device_id' => 'sensor-lux',          'type' => 'real', 'measurement' => 'luminosidad',          'unit' => 'lux', 'min' => 20000, 'max' => 55000, 'rule_name' => 'Luminosidad fuera de rango',          'meta' => ['zona' => 'pasillo-central', 'modelo' => 'BH1750FVI']],

            // --- Cama A (norte) ---
            ['name' => 'SEN0193 Humedad Suelo A',       'device_id' => 'sensor-suelo-a',      'type' => 'real', 'measurement' => 'humedad_suelo',        'unit' => 'V',   'min' => 1.6,   'max' => 2.2,   'rule_name' => 'Humedad suelo A fuera de rango',      'meta' => ['zona' => 'cama-a', 'modelo' => 'SEN0193']],
            ['name' => 'DS18B20 Temp Suelo A',           'device_id' => 'sensor-temp-suelo-a', 'type' => 'real', 'measurement' => 'temperatura_suelo',    'unit' => '°C',  'min' => 20,    'max' => 28,    'rule_name' => 'Temp suelo A fuera de rango',         'meta' => ['zona' => 'cama-a', 'modelo' => 'DS18B20']],
            ['name' => 'SEN0161 pH Agua A',              'device_id' => 'sensor-ph-a',         'type' => 'real', 'measurement' => 'ph_agua',              'unit' => 'pH',  'min' => 6.0,   'max' => 7.5,   'rule_name' => 'pH agua A fuera de rango',            'meta' => ['zona' => 'cama-a', 'modelo' => 'SEN0161-V2']],
            ['name' => 'AS7341 Color Boton A',           'device_id' => 'sensor-color-a',      'type' => 'real', 'measurement' => 'color_boton',          'unit' => 'nm',  'min' => 450,   'max' => 650,   'rule_name' => 'Color boton A fuera de rango',        'meta' => ['zona' => 'cama-a', 'modelo' => 'AS7341']],
            ['name' => 'VL53L1X Altura Tallo A',         'device_id' => 'sensor-altura-a',     'type' => 'real', 'measurement' => 'altura_tallo',         'unit' => 'mm',  'min' => 100,   'max' => 1500,  'rule_name' => 'Altura tallo A fuera de rango',       'meta' => ['zona' => 'cama-a', 'modelo' => 'VL53L1X']],

            // --- Cama B (sur) ---
            ['name' => 'SEN0193 Humedad Suelo B',       'device_id' => 'sensor-suelo-b',      'type' => 'real', 'measurement' => 'humedad_suelo',        'unit' => 'V',   'min' => 1.6,   'max' => 2.2,   'rule_name' => 'Humedad suelo B fuera de rango',      'meta' => ['zona' => 'cama-b', 'modelo' => 'SEN0193']],
            ['name' => 'DS18B20 Temp Suelo B',           'device_id' => 'sensor-temp-suelo-b', 'type' => 'real', 'measurement' => 'temperatura_suelo',    'unit' => '°C',  'min' => 20,    'max' => 28,    'rule_name' => 'Temp suelo B fuera de rango',         'meta' => ['zona' => 'cama-b', 'modelo' => 'DS18B20']],
            ['name' => 'SEN0161 pH Agua B',              'device_id' => 'sensor-ph-b',         'type' => 'real', 'measurement' => 'ph_agua',              'unit' => 'pH',  'min' => 6.0,   'max' => 7.5,   'rule_name' => 'pH agua B fuera de rango',            'meta' => ['zona' => 'cama-b', 'modelo' => 'SEN0161-V2']],
            ['name' => 'AS7341 Color Boton B',           'device_id' => 'sensor-color-b',      'type' => 'real', 'measurement' => 'color_boton',          'unit' => 'nm',  'min' => 450,   'max' => 650,   'rule_name' => 'Color boton B fuera de rango',        'meta' => ['zona' => 'cama-b', 'modelo' => 'AS7341']],
            ['name' => 'VL53L1X Altura Tallo B',         'device_id' => 'sensor-altura-b',     'type' => 'real', 'measurement' => 'altura_tallo',         'unit' => 'mm',  'min' => 100,   'max' => 1500,  'rule_name' => 'Altura tallo B fuera de rango',       'meta' => ['zona' => 'cama-b', 'modelo' => 'VL53L1X']],

            // --- Extractores (4 esquinas) ---
            ['name' => 'DFR0300 Extractor NE',          'device_id' => 'sensor-ext-ne',       'type' => 'real', 'measurement' => 'corriente_extractor',  'unit' => 'A',   'min' => 3.0,   'max' => 10.0,  'rule_name' => 'Corriente extractor NE fuera de rango', 'meta' => ['zona' => 'extractor-ne', 'modelo' => 'DFR0300']],
            ['name' => 'DFR0300 Extractor NO',          'device_id' => 'sensor-ext-no',       'type' => 'real', 'measurement' => 'corriente_extractor',  'unit' => 'A',   'min' => 3.0,   'max' => 10.0,  'rule_name' => 'Corriente extractor NO fuera de rango', 'meta' => ['zona' => 'extractor-no', 'modelo' => 'DFR0300']],
            ['name' => 'DFR0300 Extractor SE',          'device_id' => 'sensor-ext-se',       'type' => 'real', 'measurement' => 'corriente_extractor',  'unit' => 'A',   'min' => 3.0,   'max' => 10.0,  'rule_name' => 'Corriente extractor SE fuera de rango', 'meta' => ['zona' => 'extractor-se', 'modelo' => 'DFR0300']],
            ['name' => 'DFR0300 Extractor SO',          'device_id' => 'sensor-ext-so',       'type' => 'real', 'measurement' => 'corriente_extractor',  'unit' => 'A',   'min' => 3.0,   'max' => 10.0,  'rule_name' => 'Corriente extractor SO fuera de rango', 'meta' => ['zona' => 'extractor-so', 'modelo' => 'DFR0300']],

            // --- Temperatura foliar (pasillos norte/sur) ---
            ['name' => 'MLX90640 Temp Foliar Norte',    'device_id' => 'sensor-foliar-n',     'type' => 'real', 'measurement' => 'temperatura_foliar',   'unit' => '°C',  'min' => 24,    'max' => 34,    'rule_name' => 'Temp foliar norte fuera de rango',    'meta' => ['zona' => 'pasillo-norte', 'modelo' => 'MLX90640']],
            ['name' => 'MLX90640 Temp Foliar Sur',      'device_id' => 'sensor-foliar-s',     'type' => 'real', 'measurement' => 'temperatura_foliar',   'unit' => '°C',  'min' => 24,    'max' => 34,    'rule_name' => 'Temp foliar sur fuera de rango',      'meta' => ['zona' => 'pasillo-sur', 'modelo' => 'MLX90640']],

            // --- Gemelos digitales ---
            ['name' => 'Twin Temperatura Ambiente',      'device_id' => 'twin-temp',           'type' => 'twin', 'measurement' => 'temperatura_ambiente', 'unit' => '°C',  'min' => 22,    'max' => 32,    'rule_name' => 'Twin temp ambiente fuera de rango',   'meta' => ['twin_of' => 'sensor-temp-amb']],
            ['name' => 'Twin Humedad Ambiente',          'device_id' => 'twin-hum',            'type' => 'twin', 'measurement' => 'humedad_ambiente',     'unit' => '%',   'min' => 65,    'max' => 85,    'rule_name' => 'Twin humedad ambiente fuera de rango','meta' => ['twin_of' => 'sensor-hum-amb']],
        ];

        $credentials = [];
        foreach ($devices as $d) {
            $plainKey = 'dk_'.bin2hex(random_bytes(16));

            $device = Device::updateOrCreate(
                ['device_id' => $d['device_id']],
                [
                    'user_id'           => $user->id,
                    'name'              => $d['name'],
                    'type'              => $d['type'],
                    'measurement'       => $d['measurement'],
                    'unit'              => $d['unit'],
                    'api_key_hash'      => hash('sha256', $plainKey),
                    'sample_interval_s' => 15,
                    'metadata'          => $d['meta'],
                ]
            );

            if ($d['min'] !== null || $d['max'] !== null) {
                AlertRule::updateOrCreate(
                    ['device_id' => $device->id, 'measurement' => $d['measurement']],
                    [
                        'name'          => $d['rule_name'],
                        'min_threshold' => $d['min'],
                        'max_threshold' => $d['max'],
                    ]
                );
            }

            $credentials[] = "{$d['device_id']}: {$plainKey}";
        }

        // Escribir API keys a /var/www/docs (montado desde la carpeta docs/ del repo)
        $credPath = '/var/www/docs/DEV_CREDENTIALS.md';
        $content = "# API Keys de Desarrollo\n\n";
        $content .= "Generadas por DatabaseSeeder. NO subir al repo (gitignored).\n\n";
        $content .= "Usuario demo: demo@iot.local / password\n\n";
        $content .= "## Dispositivos\n\n";
        $content .= implode("\n", $credentials)."\n";

        if (is_dir(dirname($credPath))) {
            file_put_contents($credPath, $content);
            $this->command->info("API keys guardadas en docs/DEV_CREDENTIALS.md");
        } else {
            $this->command->warn("Carpeta /var/www/docs no existe en el contenedor.");
            $this->command->warn("  Verifica que ./docs:/var/www/docs este en docker-compose.yml.");
        }

        $this->command->info("{$user->email} creado");
        $this->command->info(count($devices)." dispositivos creados");
    }
}

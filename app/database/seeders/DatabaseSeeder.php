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

        $devices = [
            ['name' => 'DHT22 Bloque A',           'device_id' => 'sensor-temp-a',       'type' => 'real',    'measurement' => 'temperatura_ambiente', 'unit' => '°C',    'min' => 12,    'max' => 30,    'meta' => ['zona' => 'bloque-a']],
            ['name' => 'DHT22 Bloque B',           'device_id' => 'sensor-hum-a',        'type' => 'real',    'measurement' => 'humedad_ambiente',     'unit' => '%',     'min' => 50,    'max' => 85,    'meta' => ['zona' => 'bloque-a']],
            ['name' => 'Capacitivo Bloque A #1',   'device_id' => 'sensor-suelo-a1',     'type' => 'real',    'measurement' => 'humedad_sustrato',     'unit' => '%',     'min' => 30,    'max' => 80,    'meta' => ['zona' => 'bloque-a']],
            ['name' => 'Capacitivo Bloque A #2',   'device_id' => 'sensor-suelo-a2',     'type' => 'real',    'measurement' => 'humedad_sustrato',     'unit' => '%',     'min' => 30,    'max' => 80,    'meta' => ['zona' => 'bloque-a']],
            ['name' => 'MH-Z19 CO2',               'device_id' => 'sensor-co2',          'type' => 'real',    'measurement' => 'co2',                  'unit' => 'ppm',   'min' => null,  'max' => 1500,  'meta' => ['zona' => 'central']],
            ['name' => 'BH1750 Luminosidad',       'device_id' => 'sensor-lux',          'type' => 'real',    'measurement' => 'luminosidad',          'unit' => 'lux',   'min' => null,  'max' => null,  'meta' => ['zona' => 'central']],
            ['name' => 'Sonda pH sustrato',        'device_id' => 'sensor-ph',           'type' => 'real',    'measurement' => 'ph_sustrato',          'unit' => '',      'min' => 5.2,   'max' => 7.0,   'meta' => ['zona' => 'fertirriego']],
            ['name' => 'Sonda EC sustrato',        'device_id' => 'sensor-ec',           'type' => 'real',    'measurement' => 'ec',                   'unit' => 'mS/cm', 'min' => 1.0,   'max' => 3.0,   'meta' => ['zona' => 'fertirriego']],
            ['name' => 'Twin temperatura',         'device_id' => 'twin-temp',           'type' => 'twin',    'measurement' => 'temperatura_ambiente', 'unit' => '°C',    'min' => 12,    'max' => 30,    'meta' => ['twin_of' => 'sensor-temp-a']],
            ['name' => 'Twin humedad',             'device_id' => 'twin-hum',            'type' => 'twin',    'measurement' => 'humedad_ambiente',     'unit' => '%',     'min' => 50,    'max' => 85,    'meta' => ['twin_of' => 'sensor-hum-a']],
            ['name' => 'OWM temperatura ext.',     'device_id' => 'api-temp-ext',        'type' => 'api',     'measurement' => 'temperatura_exterior', 'unit' => '°C',    'min' => null,  'max' => null,  'meta' => ['provider' => 'openweathermap']],
            ['name' => 'OWM humedad ext.',         'device_id' => 'api-hum-ext',         'type' => 'api',     'measurement' => 'humedad_exterior',     'unit' => '%',     'min' => null,  'max' => null,  'meta' => ['provider' => 'openweathermap']],
            ['name' => 'Dataset temperatura 7d',   'device_id' => 'dataset-temp',        'type' => 'dataset', 'measurement' => 'temperatura_ambiente', 'unit' => '°C',    'min' => 12,    'max' => 30,    'meta' => ['source' => 'csv_7d']],
            ['name' => 'Dataset EC 7d',            'device_id' => 'dataset-ec',          'type' => 'dataset', 'measurement' => 'ec',                   'unit' => 'mS/cm', 'min' => 1.0,   'max' => 3.0,   'meta' => ['source' => 'csv_7d']],
        ];

        $credentials = [];
        foreach ($devices as $d) {
            $plainKey = 'dk_'.bin2hex(random_bytes(16));

            $device = Device::create([
                'user_id'           => $user->id,
                'name'              => $d['name'],
                'device_id'         => $d['device_id'],
                'type'              => $d['type'],
                'measurement'       => $d['measurement'],
                'unit'              => $d['unit'],
                'api_key_hash'      => hash('sha256', $plainKey),
                'sample_interval_s' => 15,
                'metadata'          => $d['meta'],
            ]);

            if ($d['min'] !== null || $d['max'] !== null) {
                AlertRule::create([
                    'device_id'     => $device->id,
                    'measurement'   => $d['measurement'],
                    'min_threshold' => $d['min'],
                    'max_threshold' => $d['max'],
                ]);
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
            $this->command->info("✓ API keys guardadas en docs/DEV_CREDENTIALS.md");
        } else {
            $this->command->warn("⚠ Carpeta /var/www/docs no existe en el contenedor.");
            $this->command->warn("  Verifica que ./docs:/var/www/docs esté en docker-compose.yml.");
        }

        $this->command->info("✓ {$user->email} creado");
        $this->command->info("✓ ".count($devices)." dispositivos creados");
    }
}

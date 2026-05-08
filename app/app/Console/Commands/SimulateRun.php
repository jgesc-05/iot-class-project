<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\SimulatorService;
use Illuminate\Console\Command;

// Comando que corre en loop generando metricas simuladas
// para todos los dispositivos con simulacion activa.
// Equivalente al loop de simulators/base.py pero en PHP.

class SimulateRun extends Command
{
    protected $signature = 'simulate:run';
    protected $description = 'Genera metricas simuladas en loop para dispositivos con simulacion activa';

    public function handle(): void
    {
        $this->info('Simulador iniciado. Ctrl+C para detener.');

        while (true) {
            $deviceIds = SimulatorService::getSimulatingDeviceIds();

            foreach ($deviceIds as $deviceId) {
                $device = Device::where('device_id', $deviceId)->first();

                if (!$device) {
                    SimulatorService::stop($deviceId);
                    continue;
                }

                SimulatorService::tick($device->device_id, $device->measurement);
            }

            if (count($deviceIds) > 0) {
                $this->line('[' . now()->format('H:i:s') . '] Tick para ' . count($deviceIds) . ' dispositivo(s)');
            }

            sleep(5);
        }
    }
}

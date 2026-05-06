<div class="p-6 space-y-6">
    {{-- Vista que muestra la key una vez --}}
    @if(session('new_api_key'))
        <div class="bg-yellow-100 border border-yellow-400 p-4 rounded" x-init="setTimeout(() => show = false, 8000)">
            <strong>Guarda esta API key ahora.</strong> No podrás verla de nuevo:
            <code class="block mt-2 bg-white p-2 break-all">
                {{ session('new_api_key') }}
            </code>
        </div>
    @endif


    {{-- Header con info del dispositivo --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $device->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $device->device_id }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                {{ $device->status === 'active'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800' }}">
                {{ $device->status === 'active' ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-sm">
            <div>
                <dt class="text-gray-500">Tipo</dt>
                <dd class="text-gray-900 font-medium">{{ $device->type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Medición</dt>
                <dd class="text-gray-900 font-medium">{{ $device->measurement }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unidad</dt>
                <dd class="text-gray-900 font-medium">{{ $device->unit }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Intervalo</dt>
                <dd class="text-gray-900 font-medium">{{ $device->sample_interval_s }}s</dd>
            </div>
        </dl>
    </div>

    {{-- Valor actual --}}
    <div wire:poll.5s class="bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Valor actual</h2>
        @if ($this->latestMetric)
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-5xl font-bold text-indigo-600">
                    {{ number_format((float) $this->latestMetric->value, 2) }}
                </span>
                <span class="text-2xl text-gray-500">{{ $device->unit }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                Última lectura: {{ \Carbon\Carbon::parse($this->latestMetric->time)->diffForHumans() }}
            </p>
        @else
            <p class="mt-2 text-gray-500 italic">Aún no hay métricas registradas.</p>
        @endif
    </div>

    {{-- Mini-grafico de las ultimas 2 horas (Chart.js) --}}
    <div class="bg-white shadow rounded-lg p-6"
         x-data="metricsChart('{{ $device->device_id }}', '{{ $device->unit }}')"
         x-init="init()"
         wire:ignore>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                Últimas 2 horas
            </h2>
            <span class="text-xs text-gray-400" x-text="lastUpdate"></span>
        </div>
        <div class="relative" style="height: 240px;">
            <canvas x-ref="canvas"></canvas>
        </div>
        <div x-show="!hasData" class="text-center text-gray-500 italic py-8" style="display: none;">
            Sin datos en las últimas 2 horas.
        </div>
    </div>

    {{-- Acciones (botones de comandos del día 7) --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Acciones</h2>

        @if (session('ok'))
            <div class="mb-3 p-3 bg-green-50 text-green-800 rounded text-sm">
                {{ session('ok') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-3 p-3 bg-red-50 text-red-800 rounded text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <button wire:click="sendCommand('on_off', {on: true})"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                Encender
            </button>
            <button wire:click="sendCommand('on_off', {on: false})"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                Apagar
            </button>

            <div class="flex items-center gap-2 ml-2">
                <input type="number"
                       wire:model="newInterval"
                       placeholder="Segundos"
                       class="border border-gray-300 rounded px-3 py-2 text-sm w-32">
                <button wire:click="sendCommand('set_interval')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                    Aplicar intervalo
                </button>
            </div>
        </div>
    </div>

    {{-- Enlace a historial completo --}}
    <div class="text-center">
        <a href="/history?device_id={{ $device->device_id }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
            Ver historial completo
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    {{-- Tabla de ultimos 20 comandos --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
            Últimos comandos
        </h2>

        @if ($this->recentCommands->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payload</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enviado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($this->recentCommands as $cmd)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 font-medium">{{ $cmd->type }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 font-mono">{{ $cmd->payload }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @php
                                        $statusClasses = match($cmd->status) {
                                            'pending'  => 'bg-yellow-100 text-yellow-800',
                                            'executed' => 'bg-green-100 text-green-800',
                                            'failed'   => 'bg-red-100 text-red-800',
                                            default    => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses }}">
                                        {{ $cmd->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($cmd->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 italic">Aún no hay comandos enviados.</p>
        @endif
    </div>

</div>

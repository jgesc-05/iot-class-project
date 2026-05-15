<div class="p-6 space-y-6">
    {{-- Volver a la lista de dispositivos --}}
    <a href="/devices" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Ir a dispositivos
    </a>

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
    <div class="bg-white border border-stone-200 rounded-lg p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $device->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $device->device_id }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if (!$editing)
                    <button wire:click="openEdit"
                            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Editar
                    </button>
                    <button wire:click="destroy"
                            wire:confirm="Esto eliminara el dispositivo, sus reglas, alertas y comandos. Continuar?"
                            class="inline-flex items-center gap-1 text-sm text-red-500 hover:text-red-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Eliminar
                    </button>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    {{ $device->status === 'active'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800' }}">
                    {{ $device->status === 'active' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-sm">
            <div>
                <dt class="text-gray-500">Tipo</dt>
                <dd class="text-gray-900 font-medium">{{ $device->type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Medicion</dt>
                <dd class="text-gray-900 font-medium">{{ $device->measurement }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unidad</dt>
                <dd class="text-gray-900 font-medium">{{ $device->unit }}</dd>
            </div>
            @php $meta = $device->metadata ?? []; @endphp
            @if (isset($meta['sim_min']) || isset($meta['sim_max']))
                <div>
                    <dt class="text-gray-500">Rango simulacion</dt>
                    <dd class="text-gray-900 font-medium">
                        {{ $meta['sim_min'] ?? '—' }} ~ {{ $meta['sim_max'] ?? '—' }}
                    </dd>
                </div>
            @endif
        </dl>

        @if (!empty($meta['description']))
            <p class="mt-3 text-sm text-gray-500">{{ $meta['description'] }}</p>
        @endif
    </div>

    {{-- Panel de edicion --}}
    @if ($editing)
        <div class="bg-white border border-stone-200 rounded-lg p-6 space-y-4">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Editar dispositivo</h2>

            @if ($errors->any())
                <div class="p-3 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" wire:model="editName"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                @if (count($this->availableUnits) > 0)
                    {{-- Medicion con unidades predefinidas --}}
                    <select wire:model.live="editUnit"
                            class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Selecciona una unidad --</option>
                        @foreach ($this->availableUnits as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                        <option value="__custom__">Otra (personalizar)</option>
                    </select>
                    @if ($editUnit === '__custom__')
                        <input type="text" wire:model="editCustomUnit" placeholder="Escribe la unidad"
                               class="mt-2 w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @endif
                @else
                    {{-- Medicion personalizada: campo libre --}}
                    <input type="text" wire:model="editCustomUnit" placeholder="Escribe la unidad"
                           class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rangos de simulacion
                    <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">El simulador limitara los valores generados a este intervalo.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Minimo</label>
                        <input type="number" wire:model="editRangeMin" step="any" placeholder="Sin limite"
                               class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Maximo</label>
                        <input type="number" wire:model="editRangeMax" step="any" placeholder="Sin limite"
                               class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripcion
                    <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <input type="text" wire:model="editDescription" placeholder="Notas sobre el dispositivo"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       maxlength="255">
            </div>

            <div class="flex gap-3">
                <button wire:click="saveEdit"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    Guardar cambios
                </button>
                <button wire:click="cancelEdit"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    {{-- Valor actual --}}
    <div wire:poll.5s class="bg-white border border-stone-200 rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Valor actual</h2>
        @if ($this->latestMetric)
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-5xl font-bold text-green-700">
                    {{ number_format((float) $this->latestMetric->value, 2) }}
                </span>
                <span class="text-2xl text-gray-500">{{ $device->unit }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                Última lectura: {{ \Carbon\Carbon::parse($this->latestMetric->time, 'UTC')->diffForHumans() }}
            </p>
        @else
            <p class="mt-2 text-gray-500 italic">Aún no hay métricas registradas.</p>
        @endif
    </div>

    {{-- Mini-grafico de las ultimas 2 horas (Chart.js) --}}
    <div class="bg-white border border-stone-200 rounded-lg p-6"
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
    <div class="bg-white border border-stone-200 rounded-lg p-6">
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

            <div class="flex items-center gap-3 ml-2 pl-4 border-l border-stone-200">
                @if (!$simulating)
                    <button wire:click="startSimulation"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                        Iniciar Simulacion
                    </button>
                @else
                    <button wire:click="stopSimulation"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                        Detener Simulacion
                    </button>
                    <span class="flex items-center gap-2 text-sm text-purple-600">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                        </span>
                        Simulando cada {{ $device->sample_interval_s }}s
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Enlace a historial completo --}}
    <div class="text-center">
        <a href="/history?device={{ $device->device_id }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-green-700 hover:text-green-800 transition">
            Ver historial completo
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    {{-- Tabla de comandos --}}
    <div class="bg-white border border-stone-200 rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
            Comandos
        </h2>

        @if ($commands->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accion</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enviado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($commands as $cmd)
                            @php
                                $payload = $cmd->payload ?? [];

                                $typeLabel = match($cmd->type) {
                                    'on_off'           => ($payload['on'] ?? false) ? 'Encender' : 'Apagar',
                                    'set_interval'     => 'Cambiar intervalo',
                                    'calibrate_offset' => 'Calibrar offset',
                                    default            => $cmd->type,
                                };

                                $detailLabel = match($cmd->type) {
                                    'on_off'           => ($payload['on'] ?? false) ? 'Activar dispositivo' : 'Desactivar dispositivo',
                                    'set_interval'     => 'Intervalo: ' . ($payload['seconds'] ?? '?') . 's',
                                    'calibrate_offset' => 'Offset: ' . ($payload['offset'] ?? '?'),
                                    default            => json_encode($cmd->payload),
                                };

                                $statusLabel = match($cmd->status) {
                                    'pending'  => 'Pendiente',
                                    'executed' => 'Ejecutado',
                                    'failed'   => 'Fallido',
                                    default    => $cmd->status,
                                };

                                $statusClasses = match($cmd->status) {
                                    'pending'  => 'bg-yellow-100 text-yellow-800',
                                    'executed' => 'bg-green-100 text-green-800',
                                    'failed'   => 'bg-red-100 text-red-800',
                                    default    => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 font-medium">{{ $typeLabel }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $detailLabel }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{ $cmd->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($commands->hasPages())
                <div class="mt-4">
                    {{ $commands->links() }}
                </div>
            @endif
        @else
            <p class="text-gray-500 italic">Aún no hay comandos enviados.</p>
        @endif
    </div>

</div>

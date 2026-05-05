<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900">Historial de métricas</h1>
        <p class="mt-1 text-sm text-gray-500">
            Consulta las métricas de un dispositivo en un período específico.
        </p>
    </div>

    {{-- Form de filtros --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Select de dispositivo --}}
            <div>
                <label for="device-select" class="block text-sm font-medium text-gray-700 mb-1">
                    Dispositivo
                </label>
                <select id="device-select"
                        wire:model.live="deviceId"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">— Selecciona uno —</option>
                    @foreach ($this->devices as $d)
                        <option value="{{ $d->device_id }}">
                            {{ $d->name }} ({{ $d->measurement }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date picker desde --}}
            <div>
                <label for="from-date" class="block text-sm font-medium text-gray-700 mb-1">
                    Desde
                </label>
                <input id="from-date"
                       type="datetime-local"
                       wire:model.live.debounce.500ms="fromDate"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            {{-- Date picker hasta --}}
            <div>
                <label for="to-date" class="block text-sm font-medium text-gray-700 mb-1">
                    Hasta
                </label>
                <input id="to-date"
                       type="datetime-local"
                       wire:model.live.debounce.500ms="toDate"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

        </div>

        {{-- Botones de rango rápido --}}
        <div class="mt-4 flex flex-wrap gap-2">
            <button wire:click="setRange(1)"
                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                Última hora
            </button>
            <button wire:click="setRange(6)"
                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                Últimas 6 horas
            </button>
            <button wire:click="setRange(24)"
                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                Últimas 24h
            </button>
            <button wire:click="setRange(168)"
                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                Últimos 7 días
            </button>
            <button wire:click="setRange(720)"
                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                Últimos 30 días
            </button>
        </div>
    </div>

    {{-- Mensaje cuando no hay device seleccionado --}}
    @if (!$deviceId)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-sm text-yellow-800">
                Selecciona un dispositivo arriba para ver sus métricas.
            </p>
        </div>
    @else
        {{-- Cards de estadisticas (consumen /api/devices/{id}/stats) --}}
        <div x-data="historyPage()" x-init="init()">

            {{-- Loading state --}}
            <div x-show="loading" class="text-center text-gray-500 py-4" style="display: none;">
                Cargando estadísticas...
            </div>

            {{-- Cards: avg, min, max, count --}}
            <div x-show="hasData" class="grid grid-cols-2 md:grid-cols-4 gap-4" style="display: none;">
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</p>
                    <p class="mt-1 text-3xl font-bold text-indigo-600">
                        <span x-text="format(stats.avg)"></span>
                        <span class="text-base text-gray-500" x-text="unit"></span>
                    </p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Mínimo</p>
                    <p class="mt-1 text-3xl font-bold text-blue-600">
                        <span x-text="format(stats.min)"></span>
                        <span class="text-base text-gray-500" x-text="unit"></span>
                    </p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Máximo</p>
                    <p class="mt-1 text-3xl font-bold text-red-600">
                        <span x-text="format(stats.max)"></span>
                        <span class="text-base text-gray-500" x-text="unit"></span>
                    </p>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Métricas</p>
                    <p class="mt-1 text-3xl font-bold text-gray-700" x-text="stats.count"></p>
                </div>
            </div>

            {{-- Mensaje cuando no hay datos en el rango --}}
            <div x-show="!loading && !hasData" class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center" style="display: none;">
                <p class="text-sm text-gray-600">
                    No hay métricas en este rango temporal. Intenta con un rango más amplio.
                </p>
            </div>

        </div>

        {{-- Grafico de historial (Chart.js consume /api/devices/{id}/history) --}}
        <div class="bg-white shadow rounded-lg p-6"
             x-data="historyChart()"
             x-init="init()"
             wire:ignore>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Historial
                    </h2>
                    <p class="text-xs text-gray-400 mt-1" x-show="hasData" style="display: none;">
                        Bucket: <span x-text="bucketLabel"></span> · <span x-text="pointCount"></span> puntos
                    </p>
                </div>
                <span class="text-xs text-gray-400" x-show="loading" style="display: none;">
                    Cargando...
                </span>
            </div>

            <div class="relative" style="height: 320px;" x-show="hasData" x-cloak>
                <canvas x-ref="canvas"></canvas>
            </div>

            <div x-show="!loading && !hasData" class="text-center text-gray-500 italic py-12" style="display: none;">
                Selecciona un dispositivo y un rango con datos para ver el gráfico.
            </div>
        </div>

        {{-- Tabla paginada de metricas crudas --}}
        @if ($this->metrics)
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Métricas detalladas
                    </h2>
                    <span class="text-xs text-gray-400">
                        Mostrando {{ $this->metrics->count() }} de {{ $this->metrics->total() }}
                    </span>
                </div>

                @if ($this->metrics->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metadata</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($this->metrics as $m)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-mono">
                                            {{ \Carbon\Carbon::parse($m->time)->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right font-medium">
                                            {{ number_format((float) $m->value, 2) }}
                                            <span class="text-gray-400">{{ $unit }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-500 font-mono">
                                            {{ $m->metadata ? \Illuminate\Support\Str::limit($m->metadata, 60) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $this->metrics->links() }}
                    </div>
                @else
                    <p class="text-gray-500 italic text-center py-8">
                        Sin métricas en este rango.
                    </p>
                @endif
            </div>
        @endif

    @endif

</div>

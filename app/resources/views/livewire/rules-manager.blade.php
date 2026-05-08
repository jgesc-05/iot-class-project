<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reglas de alerta
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Mensajes flash --}}
        @if(session('ok'))
            <div class="p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200">
                {{ session('ok') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Errores de validacion --}}
        @if ($errors->any())
            <div class="p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario de creacion --}}
        <div class="bg-white border border-stone-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Crear nueva regla</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" wire:model="name" placeholder="Nombre de la regla"
                           class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dispositivo</label>
                    <select wire:model.live="device_id"
                            class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Selecciona --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->device_id }}">{{ $device->name }} ({{ $device->measurement }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicion</label>
                    <input type="text" wire:model="measurement" readonly
                           class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm bg-stone-50 text-gray-600">
                    <p class="text-xs text-gray-400 mt-1">Se asigna automaticamente segun el dispositivo</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimo <span class="text-gray-400 font-normal">(al menos uno obligatorio)</span></label>
                        <input type="number" step="any" wire:model="min_threshold" placeholder="Ej: 10"
                               class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximo <span class="text-gray-400 font-normal">(al menos uno obligatorio)</span></label>
                        <input type="number" step="any" wire:model="max_threshold" placeholder="Ej: 35"
                               class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
            </div>
            <button wire:click="save"
                    class="mt-4 bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                Crear Regla
            </button>
        </div>

        {{-- Tabla de reglas existentes --}}
        <div class="bg-white border border-stone-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Reglas existentes</h3>
            </div>
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rango</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-100">
                @forelse($rules as $rule)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                            {{ $rule->name ?? 'Regla sin nombre' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $rule->device->device_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $rule->measurement }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $rule->min_threshold ?? '—' }} .. {{ $rule->max_threshold ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($rule->enabled)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Activa</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-gray-500">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($rule->enabled)
                                <button wire:click="disable({{ $rule->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                    Desactivar
                                </button>
                            @else
                                <button wire:click="enable({{ $rule->id }})"
                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                    Activar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                            No hay reglas creadas
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginacion --}}
        @if ($rules->hasPages())
            <div class="mt-4">
                {{ $rules->links() }}
            </div>
        @endif
    </div>
</div>

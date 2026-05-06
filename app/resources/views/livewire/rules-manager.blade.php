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
                    <select wire:model="device_id"
                            class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Selecciona --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->device_id }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicion</label>
                    <select wire:model="measurement"
                            class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Selecciona --</option>
                        @foreach($measurements as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimo</label>
                        <input type="number" wire:model="min_threshold" placeholder="Opcional"
                               class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximo</label>
                        <input type="number" wire:model="max_threshold" placeholder="Opcional"
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
                            {{ $rule['name'] ?? 'Regla sin nombre' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $rule['device_id'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $rule['measurement'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $rule['min_threshold'] ?? '—' }} .. {{ $rule['max_threshold'] ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($rule['enabled'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Activa</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-gray-500">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($rule['enabled'])
                                <button wire:click="disable({{ $rule['id'] }})"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                    Desactivar
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
    </div>
</div>

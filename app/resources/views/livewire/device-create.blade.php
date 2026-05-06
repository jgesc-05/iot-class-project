<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear dispositivo
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">

        @if (session('ok') || session('error'))
            <div x-data="{show: true}" x-show="show"
                 x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 rounded-lg text-sm
                    {{ session('ok') ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                {{ session('ok') ?? session('error') }}
            </div>
        @endif

        <div class="bg-white border border-stone-200 rounded-lg p-6 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del dispositivo</label>
                <input type="text" wire:model="name" placeholder="Ej: sensor-temp-a" required
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model="type"
                        class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- Selecciona un tipo --</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medicion</label>
                <select wire:model="measurement"
                        class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- Selecciona una medicion --</option>
                    @foreach($measurements as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                <input type="text" wire:model="unit" placeholder="Ej: °C, %, ppm"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo de muestreo (segundos)</label>
                <input type="number" wire:model="sample_interval" placeholder="Ej: 30"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button wire:click="save"
                    class="w-full bg-green-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                Crear Dispositivo
            </button>
        </div>
    </div>
</div>

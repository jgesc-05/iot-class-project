<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear dispositivo
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
        {{-- Volver a la lista de dispositivos --}}
        <a href="/devices" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Ir a dispositivos
        </a>

        @if (session('ok') || session('error'))
            <div x-data="{show: true}" x-show="show"
                 x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 rounded-lg text-sm
                    {{ session('ok') ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                {{ session('ok') ?? session('error') }}
            </div>
        @endif

        <div class="bg-white border border-stone-200 rounded-lg p-6 space-y-4">

            @if ($errors->any())
                <div class="p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del dispositivo</label>
                <input type="text" wire:model="name" placeholder="Ej: sensor-temp-a"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model="type"
                        class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- Selecciona un tipo --</option>
                    <option value="real">Real (dispositivo fisico)</option>
                    <option value="twin">Twin (gemelo digital)</option>
                    <option value="api">API (fuente externa)</option>
                    <option value="dataset">Dataset (datos historicos)</option>
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
                <select wire:model="unit"
                        class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- Selecciona una unidad --</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                    <option value="__custom__">Otra (personalizar)</option>
                </select>
                @if($unit === '__custom__')
                    <input type="text" wire:model="customUnit" placeholder="Escribe la unidad"
                           class="mt-2 w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo de muestreo (segundos)</label>
                <input type="number" wire:model="sample_interval" placeholder="Ej: 30" min="1"
                       class="w-full border border-stone-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button wire:click="save"
                    class="w-full bg-green-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                Crear Dispositivo
            </button>
        </div>
    </div>
</div>

{{-- Pagina /dashboards: muestra dashboards de Grafana embebidos con tabs --}}
<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboards de Grafana
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        {{-- Tabs de navegacion --}}
        <div class="border-b border-gray-200 mb-4">
            <nav class="flex space-x-4" aria-label="Tabs">
                @foreach ($dashboards as $key => $dash)
                    <button
                        wire:click="setActive('{{ $key }}')"
                        class="px-4 py-2 text-sm font-medium rounded-t-md border-b-2 transition
                            {{ $active === $key
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        {{ $dash['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Iframe del dashboard activo - ocupa toda la altura disponible --}}
        <div class="bg-white rounded-lg shadow overflow-hidden" style="height: calc(100vh - 200px);">
            <iframe
                src="{{ $iframeUrl }}"
                width="100%"
                height="100%"
                frameborder="0"
                class="w-full h-full"
            ></iframe>
        </div>
    </div>
</div>

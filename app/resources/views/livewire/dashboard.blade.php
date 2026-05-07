{{-- Pagina principal /dashboard: inicio del invernadero --}}
<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Inicio
        </h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">

        {{-- Banner de bienvenida --}}
        <div class="bg-green-700 rounded-lg p-6 sm:p-8 mb-8 text-white">
            <h1 class="text-2xl font-bold mb-1">Greenhouse Monitor</h1>
            <p class="text-green-200 text-sm">
                Monitoreo IoT para invernadero de rosas Freedom y Explorer
            </p>
        </div>

        {{-- Cards de metricas --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <a href="{{ route('devices.index') }}" class="bg-white border border-stone-200 rounded-lg p-5 hover:border-green-300 hover:shadow-sm transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dispositivos</span>
                    <span class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $totalDevices }}</div>
                <p class="text-xs text-gray-400 mt-1">Registrados en el sistema</p>
            </a>

            <a href="{{ route('devices.index') }}?status=active" class="bg-white border border-stone-200 rounded-lg p-5 hover:border-sky-300 hover:shadow-sm transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Activos</span>
                    <span class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-sky-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.425 2.121a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $activeDevices }}</div>
                <p class="text-xs text-gray-400 mt-1">Dispositivos enviando datos</p>
            </a>

            <a href="{{ route('devices.index') }}?status=inactive" class="bg-white border border-stone-200 rounded-lg p-5 hover:border-amber-300 hover:shadow-sm transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Inactivos</span>
                    <span class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $inactiveDevices }}</div>
                <p class="text-xs text-gray-400 mt-1">Dispositivos detenidos</p>
            </a>

            <a href="/alerts" class="bg-white border border-stone-200 rounded-lg p-5 hover:border-red-300 hover:shadow-sm transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Alertas activas</span>
                    <span class="w-9 h-9 {{ $pendingAlerts > 0 ? 'bg-red-50' : 'bg-stone-100' }} rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 {{ $pendingAlerts > 0 ? 'text-red-500' : 'text-gray-400' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-bold {{ $pendingAlerts > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $pendingAlerts }}</div>
                <p class="text-xs text-gray-400 mt-1">{{ $pendingAlerts > 0 ? 'Requieren atencion' : 'Todo en orden' }}</p>
            </a>
        </div>

        {{-- Accesos rapidos --}}
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Accesos rapidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            <a href="{{ route('devices.index') }}"
               class="bg-white border border-stone-200 rounded-lg p-5 hover:border-green-300 hover:shadow-sm transition group">
                <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 group-hover:text-green-700 transition">Dispositivos</h4>
                <p class="text-xs text-gray-400 mt-1">Ver y gestionar todos los dispositivos</p>
            </a>

            <a href="{{ route('dashboards.index') }}"
               class="bg-white border border-stone-200 rounded-lg p-5 hover:border-green-300 hover:shadow-sm transition group">
                <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 group-hover:text-green-700 transition">Dashboards Grafana</h4>
                <p class="text-xs text-gray-400 mt-1">Visualizar graficos en tiempo real</p>
            </a>

            <a href="/alerts"
               class="bg-white border border-stone-200 rounded-lg p-5 hover:border-green-300 hover:shadow-sm transition group">
                <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 group-hover:text-green-700 transition">Alertas</h4>
                <p class="text-xs text-gray-400 mt-1">Revisar y resolver alertas del sistema</p>
            </a>
        </div>
    </div>
</div>

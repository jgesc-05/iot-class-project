{{-- Sidebar de navegación lateral - Día 9 (A.9.2) --}}
{{-- Convive con la nav top de Breeze. Resalta el ítem activo. --}}

<aside class="w-64 bg-white border-r border-gray-200 min-h-screen p-4">
    <nav class="space-y-1">
        @php
            $items = [
                ['label' => 'Dashboard',    'route' => 'dashboard',      'icon' => 'home'],
                ['label' => 'Dispositivos', 'route' => 'devices.index',  'icon' => 'devices'],
                ['label' => 'Alertas',      'route' => null,             'icon' => 'bell',   'href' => '/alerts',      'pattern' => 'alerts*'],
                ['label' => 'Reglas',       'route' => null,             'icon' => 'shield', 'href' => '/alert-rules', 'pattern' => 'alert-rules*'],
                ['label' => 'Historial',    'route' => null,             'icon' => 'chart',  'href' => '/history',     'pattern' => 'history*'],
            ];
        @endphp

        @foreach ($items as $item)
            @php
                $href = $item['route'] && \Illuminate\Support\Facades\Route::has($item['route'])
                    ? route($item['route'])
                    : ($item['href'] ?? '#');

                $isActive = $item['route']
                    ? request()->routeIs($item['route'])
                    : request()->is($item['pattern'] ?? '');

                $base = 'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition';
                $activeClasses = 'bg-indigo-50 text-indigo-700';
                $inactiveClasses = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';
            @endphp

            <a href="{{ $href }}" class="{{ $base }} {{ $isActive ? $activeClasses : $inactiveClasses }}">
                <span class="w-5 h-5 flex-shrink-0">
                    @switch($item['icon'])
                        @case('home')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            @break
                        @case('devices')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                            </svg>
                            @break
                        @case('bell')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            @break
                        @case('shield')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            @break
                        @case('chart')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            @break
                    @endswitch
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>

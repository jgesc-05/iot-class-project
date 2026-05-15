<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dispositivos
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        @if(session('ok'))
            <div class="mb-4 p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200">
                {{ session('ok') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <p class="text-sm text-gray-500">{{ count($devices) }} dispositivos {{ $status ? "($status)" : 'registrados' }}</p>
                @if($status)
                    <a href="{{ route('devices.index') }}" class="text-xs text-green-600 hover:underline">Ver todos</a>
                @endif
            </div>
            <a href="{{ route('devices.create') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                + Crear dispositivo
            </a>
        </div>

        <div wire:poll.5s class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($devices as $row)
                <a href="{{ route('devices.show', $row['device']->id) }}"
                   class="bg-white border border-stone-200 rounded-lg p-5 hover:border-green-300 hover:shadow-sm transition block">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-semibold text-gray-800">{{ $row['device']->name }}</h3>
                        @php
                            $typeColors = match($row['device']->type) {
                                'real'    => 'bg-green-50 text-green-700',
                                'twin'    => 'bg-sky-50 text-sky-700',
                                'api'     => 'bg-amber-50 text-amber-700',
                                'dataset' => 'bg-purple-50 text-purple-700',
                                default   => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $typeColors }}">
                            {{ $row['device']->type }}
                        </span>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mt-3">
                        {{ $row['last']->value ?? '—' }}
                        <span class="text-base font-normal text-gray-400">{{ $row['device']->unit }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-2">
                        {{ $row['last']?->time
                            ? \Carbon\Carbon::parse($row['last']->time, 'UTC')->diffForHumans()
                            : 'sin datos' }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

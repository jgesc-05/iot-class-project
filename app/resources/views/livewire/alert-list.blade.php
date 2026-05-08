<div @if($filter === 'pending') wire:poll.5s @endif>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Alertas
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- Tabs de filtro --}}
        <div class="flex gap-1 mb-4">
            <button wire:click="setFilter('pending')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition
                        {{ $filter === 'pending' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border border-stone-200 hover:bg-stone-50' }}">
                Pendientes
            </button>
            <button wire:click="setFilter('resolved')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition
                        {{ $filter === 'resolved' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border border-stone-200 hover:bg-stone-50' }}">
                Resueltas
            </button>
            <button wire:click="setFilter('all')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition
                        {{ $filter === 'all' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border border-stone-200 hover:bg-stone-50' }}">
                Todas
            </button>
        </div>

        <div class="bg-white border border-stone-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regla</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disparada</th>
                    @if ($filter !== 'pending')
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    @endif
                    @if ($filter !== 'resolved')
                        <th class="px-4 py-3"></th>
                    @endif
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-100">
                @forelse($alerts as $alert)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            {{ $alert->alertRule->name ?? 'Regla sin nombre' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $alert->device->device_id ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $alert->alertRule->measurement ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $alert->value }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($alert->triggered_at)->diffForHumans() }}
                        </td>
                        @if ($filter !== 'pending')
                            <td class="px-4 py-3 text-sm">
                                @if ($alert->resolved_at)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Resuelta {{ \Carbon\Carbon::parse($alert->resolved_at)->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                        @endif
                        @if ($filter !== 'resolved')
                            <td class="px-4 py-3 text-right">
                                @if (!$alert->resolved_at)
                                    <button wire:click="resolve({{ $alert->id }})"
                                            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                        Resolver
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                            @if ($filter === 'pending')
                                No hay alertas pendientes
                            @elseif ($filter === 'resolved')
                                No hay alertas resueltas
                            @else
                                No hay alertas registradas
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginacion --}}
        @if ($alerts->hasPages())
            <div class="mt-4">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>

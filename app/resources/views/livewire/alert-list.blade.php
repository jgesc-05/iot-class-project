<div wire:poll.5s>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Alertas pendientes
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-stone-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regla</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hace</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-100">
                @forelse($alerts as $alert)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            {{ $alert['rule_name'] ?? $alert->alertRule->name ?? 'Regla sin nombre' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $alert['device_id'] ?? $alert->device->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $alert['measurement'] ?? $alert->alertRule->measurement ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $alert['value'] ?? $alert->value }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($alert['triggered_at'] ?? $alert->triggered_at)->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="resolve({{ $alert['id'] ?? $alert->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                Resolver
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                            No hay alertas pendientes
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

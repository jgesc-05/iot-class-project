<div wire:poll.5s class="p-6">
    <h2 style="font-size:18px; font-weight:600; margin-bottom:16px;">
        Alertas pendientes
    </h2>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
        <tr style="background:#f3f4f6;">
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Regla</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Dispositivo</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Medición</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Valor</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Hace</th>
            <th style="padding:10px; border:1px solid #e5e7eb;"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($alerts as $alert)
            <tr>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $alert->alertRule->name ?? 'Regla sin nombre' }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $alert->device->name ?? '—' }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $alert->alertRule->measurement ?? '—' }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $alert->value }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ \Carbon\Carbon::parse($alert->triggered_at)->diffForHumans() }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    <button wire:click="resolve({{ $alert->id }})"
                            style="background:#3b82f6; color:white; padding:6px 12px; border-radius:4px; border:none; cursor:pointer;">
                        Marcar como resuelta
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="padding:10px; text-align:center; color:#9ca3af;">
                    No hay alertas pendientes
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

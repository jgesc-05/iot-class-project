<div class="p-6">
    {{-- Mensajes flash --}}
    @if(session('ok'))
        <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px;">
            {{ session('ok') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin-bottom:16px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulario --}}
    <input type="text" wire:model="name" placeholder="Nombre de la regla" required
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <select wire:model="device_id"
            style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">
        <option value="">— Selecciona un dispositivo —</option>
        @foreach($devices as $device)
            <option value="{{ $device->device_id }}">{{ $device->name }}</option>
        @endforeach
    </select>

    <select wire:model="measurement"
            style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">
        <option value="">— Selecciona una medición —</option>
        @foreach($measurements as $m)
            <option value="{{ $m }}">{{ $m }}</option>
        @endforeach
    </select>

    <input type="number" wire:model="min_threshold" placeholder="Mínimo (opcional)"
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <input type="number" wire:model="max_threshold" placeholder="Máximo (opcional)"
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <button wire:click="save"
            style="background:#22c55e; color:white; padding:10px 20px; border-radius:6px; border:none; cursor:pointer;">
        Crear Regla
    </button>

    {{-- Tabla de reglas --}}
    <h2 style="margin-top:32px; font-size:18px; font-weight:600;">Reglas existentes</h2>
    <table style="width:100%; margin-top:12px; border-collapse:collapse;">
        <thead>
        <tr style="background:#f3f4f6;">
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Nombre</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Dispositivo</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Medición</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Rango</th>
            <th style="padding:10px; border:1px solid #e5e7eb; text-align:left;">Estado</th>
            <th style="padding:10px; border:1px solid #e5e7eb;"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($rules as $rule)
            <tr>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $rule['name'] ?? 'Regla sin nombre' }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">{{ $rule['device_id'] }}</td>
                <td style="padding:10px; border:1px solid #e5e7eb;">{{ $rule['measurement'] }}</td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    {{ $rule['min_threshold'] ?? '—' }} .. {{ $rule['max_threshold'] ?? '—' }}
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    @if($rule['enabled'])
                        <span style="color:#22c55e;">● Activa</span>
                    @else
                        <span style="color:#9ca3af;">● Inactiva</span>
                    @endif
                </td>
                <td style="padding:10px; border:1px solid #e5e7eb;">
                    @if($rule['enabled'])
                        <button wire:click="disable({{ $rule['id'] }})"
                                style="background:#ef4444; color:white; padding:6px 12px; border-radius:4px; border:none; cursor:pointer;">
                            Desactivar
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="padding:10px; text-align:center; color:#9ca3af;">
                    No hay reglas creadas
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

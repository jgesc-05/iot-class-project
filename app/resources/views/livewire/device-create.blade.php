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
    <input type="text" wire:model="name" placeholder="Nombre del dispositivo" required
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <select wire:model="type"
            style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">
        <option value="">— Selecciona un tipo de dispositivo —</option>
        @foreach($types as $type)
            <option value="{{ $type }}">{{ $type }}</option>
        @endforeach
    </select>

    <select wire:model="measurement"
            style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">
        <option value="">— Selecciona una medición —</option>
        @foreach($measurements as $m)
            <option value="{{ $m }}">{{ $m }}</option>
        @endforeach
    </select>

    <input type="text" wire:model="unit" placeholder="Unidad (ej: °C, %, ppm)"
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <input type="number" wire:model="sample_interval" placeholder="Intervalo de muestreo (segundos)"
           style="border:1px solid #ccc; padding:8px; border-radius:6px; width:100%; margin-bottom:12px;">

    <button wire:click="save"
            style="background:#22c55e; color:white; padding:10px 20px; border-radius:6px; border:none; cursor:pointer;">
        Crear Dispositivo
    </button>
</div>

{{-- Logo del proyecto: icono de planta + nombre --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-600">
        {{-- Heroicon: hoja/planta estilizada --}}
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0 0-8-4-8-11a8 8 0 0116 0c0 7-8 11-8 11z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V11" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14c1.5-1.5 3-3 3-3" />
    </svg>
    <span class="font-semibold text-lg text-gray-800">Greenhouse Monitor</span>
</div>

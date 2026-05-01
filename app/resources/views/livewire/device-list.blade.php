<div wire:poll.5s class="grid grid-cols-3 gap-4 p-6">
    @foreach($devices as $row)
        <a href="{{ route('devices.show', $row['device']->id) }}"
           class="p-4 border rounded hover:shadow-md transition block">
            <h3 class="font-semibold">{{ $row['device']->name }}</h3>
            <div class="text-3xl mt-2">
                {{ $row['last']->value ?? '—' }} {{ $row['device']->unit }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
                {{ $row['last']?->time
                    ? \Carbon\Carbon::parse($row['last']->time)->diffForHumans()
                    : 'sin datos' }}
            </div>
        </a>
    @endforeach
</div>

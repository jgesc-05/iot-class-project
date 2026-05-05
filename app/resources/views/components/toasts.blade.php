<div>
    @if (session('ok') || session('error'))
        <div x-data="{show: true}" x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 p-4 rounded shadow-lg
                {{ session('ok') ? 'bg-green-100' : 'bg-red-100' }}">
            {{ session('ok') ?? session('error') }}
        </div>
    @endif
</div>

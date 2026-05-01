<div class="space-y-4">
<button wire:click="sendCommand('on_off', {on: true})"
        class="bg-green-500 text-white px-4 py-2 rounded">
    Encender
</button>
<button wire:click="sendCommand('on_off', {on: false})"
        class="bg-red-500 text-white px-4 py-2 rounded">
    Apagar
</button>
<div>
    <input type="number" wire:model="newInterval" placeholder="Segundos">
    <button wire:click="sendCommand('set_interval', {seconds: newInterval})"
            class="bg-blue-500 text-white px-4 py-2 rounded">
        Aplicar intervalo
    </button>
</div>
</div>

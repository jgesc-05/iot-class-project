{{-- En livewire/dashboard.blade.php --}}
<div>
    @if(isset($iframeUrl))
        <iframe src="{{ $iframeUrl }}" width="100%" height="500"></iframe>
    @else
        <p>La URL no se ha generado correctamente.</p>
    @endif
</div>

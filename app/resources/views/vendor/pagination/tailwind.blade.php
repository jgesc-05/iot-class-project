@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginacion" class="flex items-center justify-between">
        {{-- Mobile: solo anterior / siguiente --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-stone-200 rounded-lg cursor-default">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-stone-200 rounded-lg hover:bg-stone-50 transition">
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-stone-200 rounded-lg hover:bg-stone-50 transition">
                    Siguiente
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-400 bg-white border border-stone-200 rounded-lg cursor-default">
                    Siguiente
                </span>
            @endif
        </div>

        {{-- Desktop: completo --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500">
                @if ($paginator->firstItem())
                    Mostrando <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    a <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    de <span class="font-medium">{{ $paginator->total() }}</span> resultados
                @else
                    Sin resultados
                @endif
            </p>

            <span class="inline-flex items-center gap-1">
                {{-- Anterior --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-white border border-stone-200 rounded-lg cursor-default">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 text-gray-500 bg-white border border-stone-200 rounded-lg hover:bg-stone-50 hover:text-green-700 transition" aria-label="Anterior">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                {{-- Numeros de pagina --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-semibold text-white bg-green-600 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-600 bg-white border border-stone-200 rounded-lg hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition" aria-label="Ir a pagina {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Siguiente --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 text-gray-500 bg-white border border-stone-200 rounded-lg hover:bg-stone-50 hover:text-green-700 transition" aria-label="Siguiente">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-white border border-stone-200 rounded-lg cursor-default">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif

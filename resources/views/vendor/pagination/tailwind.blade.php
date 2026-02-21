@if ($paginator->hasPages())
    <nav class="flex flex-col sm:flex-row items-center justify-between gap-3" role="navigation" aria-label="Pagination">

        {{-- Info text --}}
        <p class="text-sm text-warm-gray order-2 sm:order-1">
            Показано
            <span class="font-medium text-dark">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-dark">{{ $paginator->lastItem() }}</span>
            из <span class="font-medium text-dark">{{ $paginator->total() }}</span>
        </p>

        {{-- Page buttons --}}
        <div class="flex items-center gap-1 order-1 sm:order-2">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="w-9 h-9 flex items-center justify-center rounded-lg text-warm-gray/30 cursor-not-allowed select-none">
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="w-9 h-9 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors"
                    aria-label="Предыдущая">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                {{-- Separator "..." --}}
                @if (is_string($element))
                    <span class="w-9 h-9 flex items-center justify-center text-warm-gray text-sm select-none">{{ $element }}</span>
                @endif

                {{-- Page links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center rounded-lg gradient-gold text-dark text-sm font-semibold shadow-sm select-none"
                                aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="w-9 h-9 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors text-sm">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="w-9 h-9 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors"
                    aria-label="Следующая">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            @else
                <span class="w-9 h-9 flex items-center justify-center rounded-lg text-warm-gray/30 cursor-not-allowed select-none">
                    <i class="fas fa-chevron-right text-xs"></i>
                </span>
            @endif

        </div>
    </nav>
@endif

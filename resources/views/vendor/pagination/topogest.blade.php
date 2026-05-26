@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="flex-1 flex items-center justify-center">
        <span class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="relative inline-flex items-center px-3 py-2 rounded-l-md bg-white/10 text-white cursor-default">
                        ‹
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 rounded-l-md bg-[#003366] text-white hover:bg-[#002244]">
                    ‹
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="relative inline-flex items-center px-4 py-2 bg-white/10 text-white">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="relative inline-flex items-center px-4 py-2 bg-[#003366] text-white font-bold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 bg-white/10 text-white hover:bg-white/20">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 rounded-r-md bg-[#003366] text-white hover:bg-[#002244']">
                    ›
                </a>
            @else
                <span aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="relative inline-flex items-center px-3 py-2 rounded-r-md bg-white/10 text-white cursor-default">
                        ›
                    </span>
                </span>
            @endif
        </span>
    </div>
</nav>
@endif

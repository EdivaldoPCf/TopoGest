@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="flex-1 flex items-center justify-center">
        <span class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="relative inline-flex items-center px-4 py-2 rounded-l-md bg-slate-200 text-slate-400 cursor-default font-bold">
                        ‹
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-4 py-2 rounded-l-md bg-[#003366] text-white font-bold hover:bg-[#002244] shadow-md transition">
                    ‹
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="relative inline-flex items-center px-4 py-2 bg-slate-200 text-[#003366] font-bold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="relative z-10 inline-flex items-center px-5 py-2 bg-[#00E500] text-black font-black shadow-lg scale-110">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 bg-slate-200 text-[#003366] font-bold hover:bg-slate-300 transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-4 py-2 rounded-r-md bg-[#003366] text-white font-bold hover:bg-[#002244] shadow-md transition">
                    ›
                </a>
            @else
                <span aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="relative inline-flex items-center px-4 py-2 rounded-r-md bg-slate-200 text-slate-400 cursor-default font-bold">
                        ›
                    </span>
                </span>
            @endif
        </span>
    </div>
</nav>
@endif

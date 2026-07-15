@if ($paginator->hasPages())
<ul class="dataTable-pagination-list">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <li class="pager disabled"><a href="#">‹</a></li>
    @else
        <li class="pager"><a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a></li>
    @endif

    {{-- Page links --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <li class="ellipsis"><a href="#">…</a></li>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="active"><a href="#">{{ $page }}</a></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <li class="pager"><a href="{{ $paginator->nextPageUrl() }}" rel="next">›</a></li>
    @else
        <li class="pager disabled"><a href="#">›</a></li>
    @endif
</ul>
@endif

@if($paginator->hasPages())
    <nav class="cm-pager">
        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page == $paginator->currentPage())<span class="cur">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
        @endforeach
    </nav>
@endif

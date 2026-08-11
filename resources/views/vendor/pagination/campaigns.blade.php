@if ($paginator->hasPages())
    <div class="catalog__pagination">
        <ul>
            <a class="catalog__pagination-before" href="{{ ($paginator->onFirstPage()) ? ' javascript:void(0)' : $paginator->previousPageUrl() }}"></a>
            @for ($i = $paginator->currentPage(); $i <= $paginator->lastPage(); $i++)
                @if ($i===$paginator->currentPage())
                    <li><a href="javascript:void(0)"> {{$i}}</a></li>
                @else
                    <li><a href="{{$paginator->url($i)}}"> {{$i}}</a></li>
                @endif

                @if (($i-$paginator->currentPage())>=10)
                    @break
                @endif
            @endfor
            <a class="catalog__pagination-after" href="{{ ($paginator->currentPage() === $paginator->lastPage()) ? ' javascript:void(0)' : $paginator->nextPageUrl() }}"></a>
        </ul>
    </div>
@endif
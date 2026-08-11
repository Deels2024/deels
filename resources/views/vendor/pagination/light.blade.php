@if ($paginator->hasPages())
    <div class="wallet-pagination">
        <button class="wallet-pagination__btn" href="{{ ($paginator->onFirstPage()) ? ' javascript:void(0)' : $paginator->previousPageUrl() }}"><svg width="8" height="13" viewBox="0 0 8 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1L1 6.5L7 12"></path></svg></button>
        <ul class="wallet-pagination__list">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li><span>...</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><a href="javascript:void(0)" class="active"> {{$page}}</a></li>
                        @else
                            <li><a href="{{$paginator->url($page)}}"> {{$page}}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
        <button class="wallet-pagination__btn" href="{{ ($paginator->currentPage() === $paginator->lastPage()) ? ' javascript:void(0)' : $paginator->nextPageUrl() }}"><svg width="8" height="13" viewBox="0 0 8 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12L7 6.5L1 1"></path></svg></button>
    </div>
@endif

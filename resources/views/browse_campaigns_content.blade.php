<div class="catalog__content flex" style="justify-content: flex-start;">
    @foreach($campaigns as $campaign)
        <div class="bank__item catalog__content-item">
            <div class="campaign_image_in_list_container"
                 style="background-image: url('{{ $campaign->feature_img_url()->feature_image }}');"
            >
                @if ((int)$campaign->percent_raised() >= 100)
                    @include('inc.fully_banner')
                @endif
            </div>
            <div class="bank__content">
                <a href="{{route('campaign_single', $campaign->slug)}}" style="display: block; color: inherit; text-decoration: none;">
                    <div class="bank__title">
                        <div class="bank__title-text">
                            {{$campaign->title}}
                        </div>
                        <span class="bank__title-blur">{{$campaign->title}}</span>
                    </div>
                    <div class="bank__purpose">
                        Цель: {!! get_amount($campaign->goal) !!}
                        <span class="bank__purpose-blur">Цель: {!! get_amount($campaign->goal) !!}</span>
                    </div>
                    <div class="bank__text">Прогресс: {!! $campaign->percent_raised(); !!}%</div>
                    <div class="bank__text">Осталось дней: - ∞</div>
                    <div class="bank__text">
                        Финансировано: {!! get_amount($campaign->success_payments->sum('amount')) !!}</div>
                </a>
                <a href="{{ route('user.profile', $campaign->user->id) }}" class="bank__user" style="color: inherit; text-decoration: none;">
                    <img class="bank__img circle-img" src="{!! $campaign->user->avatar() !!}"/>
                    <div class="bank__user-text">{{$campaign->user->fullname}}</div>
                </a>
            </div>
        </div>
    @endforeach
</div>
@if ($campaigns->lastPage() > 1)
    <div class="catalog__pagination">
        <ul>
            <a class="catalog__pagination-before" href="{{ ($campaigns->currentPage() === 1) ? ' javascript:void(0)' : $campaigns->previousPageUrl() }}"></a>
            @if($campaigns->currentPage()>1)
                <li><a href="{{$campaigns->withQueryString()->url(1)}}">1</a>...</li>
            @endif
            @for ($i = $campaigns->currentPage(); $i <= $campaigns->lastPage(); $i++)

                @if ($i===$campaigns->currentPage())
                    <li><a href="javascript:void(0)"> {{$i}}</a></li>
                @else
                    <li><a href="{{$campaigns->withQueryString()->url($i)}}"> {{$i}}</a></li>
                @endif

                @if (($i-$campaigns->currentPage())>=10)
                    <li>...<a href="{{$campaigns->withQueryString()->url($campaigns->lastPage())}}">{{$campaigns->lastPage()}}</a></li>
                    @break
                @endif
            @endfor
            <a class="catalog__pagination-after" href="{{ ($campaigns->currentPage() === $campaigns->lastPage()) ? ' javascript:void(0)' : $campaigns->nextPageUrl() }}"></a>
        </ul>
    </div>
@endif

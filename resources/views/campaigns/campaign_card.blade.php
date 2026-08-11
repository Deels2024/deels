<a href="{{route('campaign_single', $campaign->slug)}}" class="bank__item catalog__content-item">
    <div class="campaign_image_in_list_container"
         style="background-image: url('{{ $campaign->feature_img_url()->thumbnail }}');"
    >
        @if ((int)$campaign->percent_raised() >= 100)
            @include('inc.fully_banner')
        @endif
    </div>
    <div class="bank__content">
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
        <div class="bank__user">
            <img class="bank__img magnific_image circle-img" src="{!! $campaign->user->avatar() !!}"/>
            <div class="bank__user-text">{{$campaign->user->fullname}}</div>
        </div>
    </div>
</a>
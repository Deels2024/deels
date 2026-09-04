<div class="kpromo-item" onclick="window.location='{{route('deels.public.campaigns.show', $campaign->slug)}}'">
    <div class="kpromo-item__img" style="background-size: cover; background-position: center; cursor:pointer; background-image: url('{{ $campaign->feature_img_url()->thumbnail ?? $campaign->feature_img_url()->feature_image }}')"></div>

    <div class="kpromo-item__content">
        <h3 class="kpromo-item__title">{{$campaign->title}}</h3>
        <div class="kpromo-item__info">Цель: {!! get_amount($campaign->goal) !!}</div>
        <div class="kpromo-item__info">Прогресс: 100%</div>
        <div class="kpromo-item__info">Осталось дней: - ∞</div>
        <div class="kpromo-item__info">Финансировано:<br>{!! get_amount($campaign->goal) !!}</div>
    </div>
</div>

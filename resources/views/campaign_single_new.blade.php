@extends('layouts.neon.app')
@php
    $meta_title = 'Копилка ID'.$campaign->id;
    if(! empty($title)) {
        $meta_title = 'Накопить на: '.$title.', цель - собрать '.number_format($campaign->goal, 0).' руб.';
    }
//    $meta_description = 'С помощью платформы DEELS '.$campaign->user->fullname.' копит на свою мечту. Желание: брекет '.$campaign->title.', цель - собрать '.number_format($campaign->goal, 0).' руб. Хочешь накопить на свою мечту? Регистрируйся в DEELS и осуществляй свои желания!';
    $meta_description = 'Коплю на '.$title.'. Поддержите мою мечту на Deels!';

    $campaignShareUrl = route('deels.public.campaigns.show', ['slug' => $campaign->slug]);

    if (!empty($campaign->user?->referral_code)) {
        $campaignShareUrl .= '?ref=' . urlencode($campaign->user->referral_code);
    }
    $campaignShareThumb = $campaign->shareThumbUrl();
@endphp

@section('title')
    @if( ! empty($title))
{{--        {{ $title }} | {{$campaign->id}}--}}
{{$meta_title}}
    @endif @parent
@endsection

@section('meta-data')

{{--    <meta name="description" content="{{$campaign->short_description ?: $campaign->description}}"/>--}}
    <meta name="description" content="{{$meta_description}}"/>

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="{{$meta_description}}">
    {{--<meta name="twitter:site" content="@publisher_handle">--}}
    <meta name="twitter:title" content="{{ $meta_title }}">
    <meta name="twitter:description"
          content="{{$meta_description}}">
    {{--<meta name="twitter:creator" content="@author_handle" />--}}
    <!-- Twitter Summary card images must be at least 120x120px -->
    <meta name="twitter:image" content="{!! $campaign->feature_img_url()->feature_image !!}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $meta_title }}"/>
    <meta property="og:url" content="{{ route('deels.public.campaigns.show', ['slug' => $campaign->slug]) }}"/>
    <meta property="og:image" content="{{$campaign->feature_img_url()->feature_image}}"/>
    <meta property="og:type" content="article"/>
    <meta property="og:description" content="{{$meta_description}}"/>

@endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">

@endsection

@section('content')
    <div class="background__dark"></div>
    <div class="container">
        <div class="main-content__user-profile-main">
            <ul class="main-content__gallery">
                <li class="main-content__gallery-image main-content__gallery-image_first magnific_image"
                    style="background-image: url({{$campaign->feature_img_url()->original}})" data-image="{{$campaign->feature_img_url()->original}}">
                    <span class="main-content__gallery-value">{{$campaign->percent_raised()}}%</span>
                    <button class="main-content__gallery-arrow"></button>
                </li>
                {{--                <li class="main-content__gallery-image"--}}
                {{--                    style="background-image: url({{$campaign->feature_img_url()->original}})"></li>--}}
                @if ($campaign->images)
                    @foreach($campaign->images as $k=>$image)
                        <li class="main-content__gallery-image magnific_image"
                            style="background-image: url({{media_image_uri($image)->original}})" data-image="{{media_image_uri($image)->original}}"></li>
                    @endforeach
                @endif
                {{--                <li class="main-content__gallery-image">--}}
                {{--                    <span class="main-content__gallery-counter">+2</span>--}}
                {{--                </li>--}}
            </ul>

            <div class="main-content__user-profile">
                <a href="{{$campaign->user ? route('user.profile', $campaign->user->id) : ''}}" class="main-content__user-profile-head">
                    <i class="main-content__user-avatar magnific_image circle-img"
                       style="background-image: url({{$campaign->user?->avatar()}})" data-image="{{$campaign->user?->avatar()}}"></i>
                    <span class="main-content__user-name">{!! $campaign->user->fullname !!}</span>
                </a>
                <ul class="main-content__social-link-list">
                    <li>
                        <a href="https://t.me/share/url?url={{ route('deels.public.campaigns.show', ['slug' => $campaign->slug]) }}"
                           class="main-content__social-link main-content__social-link_telegram" target="_blank" style="background-size: cover"></a>
                    </li>
{{--                    <li>--}}
{{--                        <a href='https://vk.com/share.php?url={{route('campaign_single', $campaign->slug)}}&title={{$campaign->title}}'--}}
{{--                           class="main-content__social-link main-content__social-link_vk"--}}
{{--                        target="_blank"></a>--}}
{{--                    </li>--}}
                </ul>
            </div>
            <p class="main-content__category">
                <a href="{{ route('deels.public.campaigns.index') }}">Копилки</a> — <a href="{{ route('campaigns.category', ['slug' => $campaign->get_category->slug]) }}">{{$campaign->get_category->category_name}}</a><span
                        class="main-content__category-number">№ {{$campaign->id}}</span>
            </p>
            <h1 class="main-content__title">
                {!! $campaign->title !!}
            </h1>

            <div class="main-content__info">
                <p class="main-content__discription">
                    {!! safe_output($campaign->description) !!}
                </p>
                <p class="main-content__info-target">
                    Цель:<span class="main-content__info-target-price"> {!! get_amount($campaign->goal) !!}</span>
                </p>
                <dl class="main-content__stat">
                    <dt class="main-content__stat-name">Статус:</dt>
                    <dd class="main-content__stat-value">{!! $campaign->getStatus()!!}</dd>
                    <dt class="main-content__stat-name">Осталось дней:</dt>
                    <dd class="main-content__stat-value">∞</dd>
                    <dt class="main-content__stat-name">Спонсоры:</dt>
                    <dd class="main-content__stat-value">{!! $campaign->success_payments->count() !!}</dd>
                    <dt class="main-content__stat-name">Финансировано:</dt>
                    <dd class="main-content__stat-value">{!! get_amount($campaign->success_payments->sum('amount')) !!}</dd>
                </dl>


            </div>



            @php
                $campaignStories = $campaign->stories;
                $campaignViewedStoryIds = Auth::check() && $campaignStories->isNotEmpty()
                    ? \App\Models\View::where('user_id', Auth::id())
                        ->whereIn('story_id', $campaignStories->pluck('id'))
                        ->pluck('story_id')
                    : collect();
                $campaignStories->each(function ($story) use ($campaignViewedStoryIds): void {
                    $story->setAttribute('is_viewed', $campaignViewedStoryIds->contains((int) $story->id));
                });
            @endphp
            <ul class="main-content__switch">
                @if($campaignStories->isNotEmpty())
                    <li class="main-content__switch-link main-content__switch-link_stories main-content__switch-link_active">
                        <a class="main-content__switch-link" href="#campaign-stories">Истории копилки</a>
                    </li>
                @endif
                <li class="main-content__switch-link main-content__switch-link_donate {{ $campaignStories->isEmpty() ? 'main-content__switch-link_active' : '' }}">
                    <a class="main-content__switch-link" href="#campaign-donors">Поддержали</a>
                </li>
                @if(isset($show_comments))
                    <li class="main-content__switch-link main-content__switch-link_comments">
                        <a class="main-content__switch-link" href="#campaign-comments">Комментарии</a>
                    </li>
                @endif
            </ul>
            <div class="story__buttons campaign_sidebar">
                <dl class="story__price">
                    <dt class="story__price-name">Цель:</dt>
                    <dd class="story__price-value">{!! get_amount($campaign->goal) !!}</dd>
                </dl>

                @if(!in_array((int)$campaign->status, [\App\Models\Campaign::STATUS_FINISHED, \App\Models\Campaign::STATUS_ARCHIVED], true))
                    @if((Auth::id() === $campaign->user_id)  || (Auth::user() && Auth::user()->is_admin()))
                        <div class="pig-progress" style="--value: {{ (int)($campaign->health ?? \App\Models\Campaign::HEALTH_MAX) }}" data-toggle="tooltip" title="Здоровье копилки: {{ (int)($campaign->health ?? \App\Models\Campaign::HEALTH_MAX) }}">
                            <div class="pig-progress__icon"><img src="/img/piggy.png"></div>
                            <div class="pig-progress__bar">
                                <div class="pig-progress__fill"></div>
                                <div class="pig-progress__segments">
                                    <span></span><span></span><span></span><span></span><span></span>
                                    <span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                @if(Auth::id() === $campaign->user_id && (int)$campaign->status === 4)
                    <form method="post" action="{{route('wake_campaign', $campaign->id)}}">
                        @csrf
                        <button class="story__button story__button_main w-100" type="submit">
                            Разбудить
                        </button>
                    </form>
                @endif
                @if ((int)$campaign->percent_raised() < 100)
                @if(Auth::user())
                    @if(Auth::id() === $campaign->user_id)
                        <a class="story__button story__record" href="{{route('stories.create', ['campaign' => $campaign->id])}}" style="text-align: center">
                            Записать онлайн-сторис в историю копилки
                        </a>
                    @endif
                    <button class="story__button story__button_main product__btn-pay" type="button">
                        Внести донат
                    </button>
                @else
                    <a class="story__button story__button_main" href="{{route('login')}}" style="text-align: center">
                        Внести донат
                    </a>
                @endif
                    <button class="story__button product__btn-pay_auto" type="button">
                        Подписка на автоплатеж
                    </button>
                @endif
                <div class="story__button-list small">
{{--                    <button class="story__button story__button_copy-link btn__copy" type="button"></button>--}}

                    <script src="https://yastatic.net/share2/share.js"></script>
                    <div class="story__button story__share ya-share2" data-title="{{$meta_description}}" data-image="{{ $campaignShareThumb }}" data-url="{{ $campaignShareUrl }}" data-curtain data-shape="round" data-services="vkontakte,odnoklassniki,telegram,whatsapp" data-limit="0" data-more-button-type="short"></div>

                    @if(Auth::user() && isset($campaign->user->id))
                        <button class="story__button story__button_like add_campaign_like d-flex {{$campaign->isLiked(Auth::user()->id) ? 'active' : ''}}" type="button" data-route="{{route('add_like', $campaign->slug)}}" data-campaign="{{$campaign->id}}">

                        </button>
                        <button class="story__button story__button_copy-link chat_btn d-flex" data-user="{{$campaign->user->id}}" type="button"></button>
                    @endif
                    @if(Auth::user() && isset($campaign->user->id))
                        <button class="story__button story__button_copy-link follow_button {{Auth::user()->isFollowing($campaign->user) ? 'active' : ''}}" data-user="{{$campaign->user->id}}" type="button"></button>
                    @endif
                </div>

            </div>
            @if(isset($show_view_stories))
            <div class="story__block-content">
                <div class="story__head">
                    <h2 class="story__title">Смотреть сторис</h2>
{{--                    <i class="story__clue-icon"></i>--}}
{{--                    <div class="story__clue-popup">--}}
{{--                        <p>--}}
{{--                            Донатить посто так не хочется? Пользователь подготовил для--}}
{{--                            тебя что-то интересное, донать и наслаждайся видео!--}}
{{--                            <button class="story__clue-popup-close"></button>--}}
{{--                        </p>--}}
{{--                    </div>--}}
                </div>
                <?php /*
                <div class="story__content story__content_closed">
                    <div style="width: 202px; height: 345px" class="stories_carousel">
                        <video style="width: 200px" width="204" height="345" src="/thanks/adasd.mov" controls></video>
                        <video style="width: 200px" width="204" height="345" src="/thanks/adasd.mov" controls></video>
                        <video style="width: 200px" width="204" height="345" src="/thanks/adasd.mov" controls></video>
                        <video style="width: 200px" width="204" height="345" src="/thanks/adasd.mov" controls></video>
                        <img class="story__content-image" src="/images/story/image 1425.jpg" alt="story">
                    </div>
                    <button class="story__content-button story__content-button_back"></button>
                    <button class="story__content-button story__content-button_next"></button>
                </div>
                */?>
                @php
                    $stories = $campaignStories;
                @endphp
                @if(count($stories))
                    <div class="story__content">
                        <div style="width: 202px; height: 345px" class="stories_carousel">
                            @foreach($stories as $story)
                                @php
                                    $is_viewed = (bool) $story->getAttribute('is_viewed');
                                @endphp
                                    <a href="#story-popup" class="copystories-item show_campaign_story show_story {{$story->paid && !$is_viewed ? 'story__content_closed' : ''}}" style="width: 202px; height: 345px" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
                                    @include('stories.parts.preview', [
                                        'story' => $story,
                                        'class' => 'copystories-item__img ' . ($story->paid && !$is_viewed ? 'blurred' : ''),
                                    ])
                                    <div class="copystories-item__content">
                                        <div class="play-btn copystories-btn"></div>
                                        @include('stories.parts.stats', ['story' => $story])
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <button class="story__content-button story__content-button_back"></button>
                        <button class="story__content-button story__content-button_next"></button>
                    </div>
                    <style>
                        .slick-list.draggable {
                            margin-right: 0!important;
                        }
                    </style>
                    <div class="story__content-buttons-slider">
                        {{--                    <button class="story__content-button-slider story__content-button-slider_active"></button>--}}
                        {{--                    <button class="story__content-button-slider"></button>--}}
                        {{--                    <button class="story__content-button-slider"></button>--}}
                        {{--                    <button class="story__content-button-slider"></button>--}}
                    </div>
                @else
                    У пользователя пока нет сторис
                @endif
            </div>
            @endif
{{--            <div class="main-content__special-block">--}}
{{--                <h2 class="main-content__special-block-title">--}}
{{--                    Специальные предложения--}}
{{--                </h2>--}}
{{--                <div class="main-content__special-block-card">--}}
{{--                    <img class="main-content__special-block-img" src="/images/cards-left/Rectangle 6569.jpg">--}}
{{--                    <p class="main-content__special-block-text">--}}
{{--                        Звонок по видеосвязи 30 минут--}}
{{--                    </p>--}}
{{--                    <span class="main-content__special-block-price">500₽</span>--}}
{{--                    <button class="main-content__special-block-button">--}}
{{--                        Внести донат--}}
{{--                    </button>--}}
{{--                    <button class="main-content__special-block-button-arrow"></button>--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="main-content__donate-comments">
                <div class="main-content__donates {{ $campaignStories->isEmpty() ? 'main-content__block_active' : '' }}" id="campaign-donors">
                    @forelse($campaign->success_payments as $payment)
                        <div class="main-content__donate">
                            <div class="main-content__donate-head">
                                <a href="{{$payment->user ? route('user.profile', $payment->user->id) : '#'}}">
                                    <ul class="main-content__donate-profile">
                                        <li>
                                            <i class="main-content__donate-avatar magnific_image circle-img"
                                               style="background-image: url({{$payment->user ? $payment->user->avatar() : '/default_avatars/avatar_2.webp'}})" data-image="{{$payment->user ? $payment->user->avatar() : '/default_avatars/avatar_2.webp'}}"></i>
                                        </li>
                                        <li>
                                            <span class="main-content__donate-name"
                                               >{{$payment->user->fullname ?? 'Пользователь удален'}}</span><span
                                                    class="main-content__donate-date">{{$payment->created_at->format('d.m.Y')}}</span>
                                        </li>
                                    </ul>
                                </a>
                                <div class="main-content__donate-right">
                                    @if((!$payment->thanks && $payment->user && $payment->user->id != $campaign->user->id && $campaign->user->id === auth()->id())  || (!$payment->thanks && !isset($payment->user)))
                                        <button class="main-content__donate-button" data-popup="popup-{{$payment->id}}"></button>
                                        <label class="main-content__donate-label-button"
                                               for="popup-{{$payment->id}}">Поблагодарить</label>
                                    @endif
                                    <span class="main-content__donate-price">+ {{$payment->amount}}₽</span>
                                    @if(!$payment->thanks && $campaign->user->id === auth()->id())
                                        <style>
                                            .main-content__donate-pop-up-button {
                                                width: 28px;
                                                height: 28px;
                                                background-size: 50%;
                                                background-position: center;
                                                border:  1px solid rgba(0, 240, 255, 0.3) !important;
                                                border-radius: 50%;
                                                /*padding: 5px;*/
                                            }
                                            .main-content__donate-pop-up-button:hover {
                                                border:  1px solid #B224EF !important;
                                            }
                                        </style>
                                        <div class="main-content__donate-pop-up main-content__donate-pop-up"
                                             id="popup-{{$payment->id}}">
                                            <button type="button"
                                                    style="background-image: url(/images/icon/ai.svg);background-size: 60%;"
                                                    onclick="$(this).parents('.main-content__donate').find('form').slideUp();$(this).parents('.main-content__donate').find('.main-content__donate-enter-ai').slideDown()"
                                                    class="main-content__donate-pop-up-button main-content__donate-pop-up-button_ai"></button>
                                            <button type="button"
                                                    onclick="$(this).parents('.main-content__donate').find('form').slideUp();$(this).parents('.main-content__donate').find('.main-content__donate-enter-text').slideDown()"
                                                    class="main-content__donate-pop-up-button main-content__donate-pop-up-button_text"></button>
{{--                                            <button type="button"--}}
{{--                                                    onclick="$(this).parents('.main-content__donate').find('form').slideUp();$(this).parents('.main-content__donate').find('.main-content__donate-enter-img').slideDown()"--}}
{{--                                                    class="main-content__donate-pop-up-button main-content__donate-pop-up-button_photo"></button>--}}
{{--                                            <button type="button"--}}
{{--                                                    style="background-image: url(/images/icon/microphone.svg);background-size: 60%;"--}}
{{--                                                    onclick="$(this).parents('.main-content__donate').find('form').slideUp();$(this).parents('.main-content__donate').find('.main-content__donate-enter-voice').slideDown()"--}}
{{--                                                    class="main-content__donate-pop-up-button main-content__donate-pop-up-button_voice"></button>--}}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($payment->thanks)
                                @if(Auth::user() && !$payment->thanks->approved && Auth::user()->id === $campaign->user_id || $payment->thanks->approved)
                                    @if($payment->thanks->payment->user_id != $campaign->user_id)
                                        <div class="main-content__donate-message  main-content__donate-message_mobile">
                                            <div class="main-content__donate-repost-icon"></div>
                                            <div class="main-content__donate-repost">
                                                <a href="{{$campaign->user ? route('user.profile', $campaign->user->id) : '#'}}">
                                                    <ul class="main-content__donate-profile main-content__donate-message_mobile">
                                                        <i class="main-content__donate-avatar magnific_image circle-img"
                                                           style="background-image: url({{$campaign->user?->avatar()}});" data-image="{{$campaign->user?->avatar()}}"></i>
                                                        <li>
                                                            <span class="main-content__donate-name">{!! $campaign->user->fullname !!}</span><span
                                                                    class="main-content__donate-date">{{$payment->thanks->created_at->format('d.m.Y')}}</span>
                                                        </li>
                                                        <li>
                                                            @if(!$payment->thanks->approved)
                                                                <div class="badge badge-moderation" style="background: #00f0ff; padding: 5px 10px; text-align: center; border-radius: 10px; font-size: 14px;">На модерации</div>
                                                            @endif
                                                        </li>
                                                    </ul>
                                                </a>

                                                @if ($payment->thanks->data['type']==='comment')
                                                    <p class="main-content__donate-text">
                                                        {{$payment->thanks->data['payload']}}
                                                    </p>
                                                @elseif ($payment->thanks->data['type']==='audio')
                                                    <div class="main-content__donate-repost-voice-message"
                                                         style="margin-bottom: 40px;">
                                                        <audio controls src="{{$payment->thanks->data['payload']}}"></audio>
                                                        {{--                                            <button class="main-content__donate-enter-button main-content__donate-enter-button_play-repost" type="button"></button>--}}
                                                        {{--                                            <img class="main-content__donate-enter-voice-wave" src="./images/icon/wave.svg">--}}
                                                        {{--                                            <img class="main-content__donate-enter-voice-wave" src="./images/icon/wave.svg">--}}
                                                        {{--                                            <span class="main-content__donate-enter-voice-time main-content__donate-enter-voice-time_repost">03:24</span>--}}
                                                    </div>
                                                @else
                                                    <img style="max-width: 500px;margin: 20px 0 40px;" alt=""
                                                         src="{{$payment->thanks->data['payload']}}"/>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endif

                            @else
                                <div class="badge badge-moderation" style="display:inline-block;padding: 5px 10px; border-radius: 10px; font-size: 14px; opacity: .5; margin-top: 15px">Ожидает благодарность</div>
                            @endif
                            <form style="display: none" method="post" action="/payments/{{$payment->id}}/thank"
                                  enctype="multipart/form-data" class="main-content__donate-enter-ai ai-generate-form">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{$payment->id}}">
                                <input type="hidden" name="ai_generate" value="1">
                                <div class="new__description form-field scenario" style="">
                                    <div class="box">
                                        <div class="box__content">
                                            <h3>Сгенерировать благодарность при помощи ИИ</h3>
                                            <p class="scenario_content">
                                                Здесь вы можете с помощью искусственного интеллекта сгенерировать благодарность за этот донат.
                                            </p>
                                            <br>
                                            <p class="scenario_content">
                                                @php
                                                    $ai_generate_cost = env('AI_THANKS_COST', 1000);
                                                    if($payment->amount >= 100) {
                                                        $ai_generate_cost = env('AI_THANKS_IMAGE_COST', 5000);
                                                    }
                                                @endphp
                                                Стоимость генерации за данный донат составит: {{number_format($ai_generate_cost, 0, ',', ' ')}}
                                                <input type="hidden" name="ai_generate_cost" value="{{$ai_generate_cost}}">
                                                {{trans_choice('numbers.coins', $ai_generate_cost ?? 0)}}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="main-content__comments-buttons" style="margin-top: 20px">
                                    <p>
                                        <button class="contact__btn btn btn_fill ai_generate_btn" data-payment-id="{{$payment->id}}">
                                            Сгенерировать за {{number_format($ai_generate_cost, 0, ',', ' ')}} {{trans_choice('numbers.coins', $ai_generate_cost ?? 0)}}
                                        </button>
                                    </p>
                                </div>


                            </form>
                            <form style="display: none" method="post" action="/payments/{{$payment->id}}/thank"
                                  enctype="multipart/form-data" class="main-content__donate-enter-text">
                                @csrf
                                <textarea name="comment" class="main-content__donate-enter"
                                          placeholder="Введите текст.." required></textarea>
                                <div class="main-content__comments-buttons">
                                    <button class="contact__btn btn btn_fill" type="submit">
                                        Отправить
                                    </button>
                                    <label class="main-content__comments-label" for="terms">
                                        Даю согласие на обработку своих персональных данных. С
                                        политикой конфеденциальности ознакомлен и согласен.
                                        <input type="checkbox" id="terms" class="main-content__comments-checkbox">
                                        <div class="main-content__comments-checkbox-checkmark"></div>
                                    </label>
                                </div>
                            </form>
{{--                            <form style="display: none" method="post" action="/payments/{{$payment->id}}/thank"--}}
{{--                                  enctype="multipart/form-data" class="main-content__donate-enter-img">--}}
{{--                                @csrf--}}
{{--                                <span--}}
{{--                                        onclick="$(this).next().click()"--}}
{{--                                        class="main-content__donate-enter-img-label main-content__donate-enter-img-label_active"--}}
{{--                                ></span>--}}
{{--                                <input--}}
{{--                                        style="display:none;"--}}
{{--                                        class="main-content__donate-enter-img-input"--}}
{{--                                        type="file"--}}
{{--                                        id="fileselect"--}}
{{--                                        onchange="alert('Изображение загружено')"--}}
{{--                                        name="image"--}}
{{--                                        accept="image/*"--}}
{{--                                        required--}}
{{--                                />--}}
{{--                                <button class="main-content__donate-submit" type="submit">--}}
{{--                                    Отправить--}}
{{--                                </button>--}}
{{--                            </form>--}}
{{--                            <form style="display: none" method="post" action="/payments/{{$payment->id}}/thank"--}}
{{--                                  enctype="multipart/form-data" class="main-content__donate-enter-voice">--}}
{{--                                @csrf--}}
{{--                                <button type="button" onclick="$(this).parent().find('.audioFile').click()"--}}
{{--                                        class="main-content__donate-enter-button main-content__donate-enter-button_record"></button>--}}
{{--                                <div class="main-content__donate-enter-voice-message">--}}
{{--                                    <img class="main-content__donate-enter-voice-wave" src="/images/icon/wave.svg">--}}
{{--                                    <img class="main-content__donate-enter-voice-wave" src="/images/icon/wave.svg">--}}
{{--                                </div>--}}
{{--                                <input style="display: none" class="audioFile" type="file" accept="audio/*"--}}
{{--                                       name="audio" required>--}}
{{--                                <button class="main-content__donate-submit" type="submit">--}}
{{--                                    Отправить--}}
{{--                                </button>--}}
{{--                            </form>--}}
                        </div>
                    @empty
                        <div class="main-content__donate main-content__donate_empty">
                            Пока никто не поддержал эту копилку. Можно стать первым.
                        </div>
                    @endforelse
                </div>

                @if($campaignStories->isNotEmpty())
                <div class="main-content__stories-block main-content__block_active mb-5" id="campaign-stories">
                    <div class="challenge-grid main-content__stories" style="--challenge-grid: repeat(4, 1fr); overflow-y: initial">
                        @foreach($campaignStories as $story)
                            @include('stories.story_item', ['story' => $story, 'challenge' => true])
                        @endforeach
                    </div>
                </div>
                @endif
                @if(isset($show_comments))
                @php
                    $comments = \App\Models\Comment::approved()->parent()->whereCampaignId($campaign->id)->with('childs_approved')->orderBy('id', 'desc')->get();
                    $comments_count = \App\Models\Comment::approved()->whereCampaignId($campaign->id)->count();
                @endphp
                <div class="main-content__comments-block mb-5" id="campaign-comments">
                    <div class="main-content__comments" style="overflow-y: initial">
                        @if($comments_count < 1)
                            <div class="comments__info">Нет комментариев, будь первым
                                <span>Нет комментариев, будь первым</span>
                            </div>
                        @else
                            @foreach($comments as $comment)
                                <div id="comment-{{$comment->id}}" class="main-content__comment">
                                    <div class="main-content__comment-head">
                                        <a href="{{$comment->author ? route('user.profile', $comment->author->id) : '#'}}">
                                            <ul class="main-content__comment-profile">
                                                <li>
                                                    @if($comment->user_id)
                                                        <i class="main-content__comment-avatar magnific_image circle-img"
                                                           style="background-image: url({{$comment->author?->avatar()}})" data-image="{{$comment->author?->avatar()}}"></i>
                                                    @else
                                                        <i class="main-content__comment-avatar magnific_image circle-img"
                                                           style="background-image: url({{avatar_by_email($comment->author_email)}})" data-image="{{avatar_by_email($comment->author_email)}}"></i>
                                                    @endif
                                                </li>
                                                <li>
                                                    <span class="main-content__comment-name">{{$comment->author_name}}</span>
                                                </li>
                                            </ul>
                                        </a>
                                        <span class="main-content__comment-date">{{$comment->created_at->format('d.m.Y')}}</span>
                                    </div>
                                    <p class="main-content__comment-text">
                                        {!! safe_output(nl2br($comment->comment)) !!}
                                    </p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <form action="{{route('post_comments', $campaign->id)}}" method="post"
                          class="main-content__comments-form">
                        @if(Auth::user())
                            @csrf
                            <h2 class="main-content__comments-form-title">
                                Ваш комментарий
                            </h2>
                        <textarea
                                class="main-content__comments-enter"
                                placeholder="Введите текст.."
                                name="comment"
                        ></textarea>
                        <div class="main-content__comments-buttons">
                            <button class="contact__btn btn btn_fill" type="submit">
                                Отправить
                            </button>
{{--                            <label class="main-content__comments-label" for="terms">--}}
{{--                                Даю согласие на обработку своих персональных данных. С--}}
{{--                                политикой конфеденциальности ознакомлен и согласен.--}}
{{--                                <input type="checkbox" id="terms" class="main-content__comments-checkbox"/>--}}
{{--                                <div class="main-content__comments-checkbox-checkmark"></div>--}}
{{--                            </label>--}}
                        </div>
                        @else
                            Вы не авторизованы! Нажмите <a href="{{route('login')}}"><u>здесь</u></a>, чтобы авторизоваться.
                        @endif
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <div class="popup__wrape_1">
        <div class="popup__modal">
            <form action="{{route('add_to_cart')}}" class="campaign__donat-form">
                <input type="hidden" name="campaign_id" value="{!! $campaign->id !!}"/>
                <div class="popup__close"><img src="/dist/images/icons/close.svg" alt=""></div>
                <div class="popup__title">Введите сумму доната</div>
                <div class="d-flex">
                <input id="input" name="amount" required value="500" min="500" type="number" class="popup__input"
                       placeholder="500 дилсов">
                    <img src="/dist/img/deels_cur.svg" class="medium_coin">
                </div>

                <div class="d-flex mt-4 align-items-center">
                <button class="popup__btn btn btn_fill" type="submit" style="margin-top: 0">Подтвердить</button>
                <span class="ml-4">100 дилсов = 1 ₽</span>
                </div>
            </form>
        </div>
    </div>

    <div class="popup__wrape_1_auto">
        <div class="popup__modal">
            <form action="{{route('add_to_cart')}}?auto=1" class="campaign__donat-form">
                <input type="hidden" name="auto" value="1"/>
                <input type="hidden" name="campaign_id" value="{!! $campaign->id !!}"/>
                <div class="popup__close"><img src="/dist/images/icons/close.svg" alt=""></div>
                <div class="popup__title">Введите сумму подписки</div>
                <div class="d-flex">
                    <input id="input" name="amount" required value="500" min="500" type="number" class="popup__input"
                           placeholder="500 дилсов">
                    <img src="/dist/img/deels_cur.svg" class="medium_coin">
                </div>

                <div class="d-flex mt-4 align-items-center">
                    <button class="popup__btn btn btn_fill" type="submit" style="margin-top: 0">Подтвердить</button>
                    <span class="ml-4">100 дилсов = 1 ₽</span>
                </div>
            </form>
        </div>
    </div>

    @include('stories.modal')

    <div class="popup popup--sm mfp-hide" id="campaign-message-popup">
        <div class="popup-head">
            <h2 class="popup-head__title text-center" id="campaign-message-popup-title">Уведомление</h2>
        </div>
        <div class="popup-body d-flex flex-column ai-center mb-7">
            <p class="text-center" id="campaign-message-popup-text"></p>
        </div>
        <div class="popup-footer text-center">
            <button class="btn btn_fill magnific_close" type="button">Подтвердить</button>
        </div>
    </div>

    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .main-content__donate.active {
            box-shadow: 0px 0px 12px 2px #B224EF;
        }
        .main-content__stories-block {
            display: none;
        }
        .main-content__stories-block.main-content__block_active {
            display: block;
        }
    </style>

    <script>
        function showCampaignMessagePopup(message, title = 'Уведомление') {
            $('#campaign-message-popup-title').text(title);
            $('#campaign-message-popup-text').text(message);

            $.magnificPopup.open({
                items: {
                    src: '#campaign-message-popup'
                },
                type: 'inline'
            });
        }

        $('body').on('click', '.magnific_close', function (e) {
            e.preventDefault();
            $.magnificPopup.close();
        });

        // $(document).ready(function () {
        //     $(document).on("click", ".btn__copy", function (e) {
        //         $("body").append('<input id="copyURL" type="text" value="" />');
        //         $("#copyURL").val(window.location.href).select();
        //         document.execCommand("copy");
        //         $("#copyURL").remove();
        //         $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span> Ссылка скопирована в буфер обмена</div>')
        //     });
        // });
        $(function () {
            @if(request('s')==='y')
                showCampaignMessagePopup('Успех! Ваш комментарий появится сразу после прохождения модерации');
            @endif

            $('body').on('click', '.add_campaign_like', function (e) {
                e.preventDefault();
                var like_btn = $(this);
                var route = $(this).attr('data-route');
                var story_id = $(this).attr('data-campaign');
                $('.add_campaign_like').toggleClass('active');
                $.ajax({
                    type: 'POST',
                    url: route,
                    data: {user_id: '{{Auth::user()->id ?? null}}', campaign_id: story_id},
                    success: function (data) {

                    }
                });
            });

            $('body').on('click', '.ai_generate_btn', function (e) {
                e.preventDefault();
                var btn = $(this);
                var form = $(this).parents('.ai-generate-form');
                var text_value = $(this).text();
                var payment_id = $(this).attr('data-payment-id');
                btn.text('Генерируем...');
                btn.attr('disabled', true);
                btn.prop('disabled', true);
                $.ajax({
                    type: 'POST',
                    url: '{{route('services.api.thanks.web')}}',
                    data: {user_id: '{{Auth::user()->id ?? null}}', payment_id: payment_id},
                    success: function (data) {
                        if(data.success) {
                            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>'+data.message+'</div>');
                            setTimeout(function(){
                                window.location.reload(1);
                            }, 2000);
                            btn.text(text_value);
                            btn.attr('disabled', false);
                            btn.prop('disabled', false);
                        } else {
                            $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.error+'</div>');
                            btn.text(text_value);
                            btn.attr('disabled', false);
                            btn.prop('disabled', false);
                        }
                    }
                });
            });

            $('body').on('click', '.follow_button', function (e) {
                e.preventDefault();
                var like_btn = $(this);
                var follow_id = $(this).attr('data-user');
                $.ajax({
                    type: 'POST',
                    url: '{{route('user.follow_toggle')}}',
                    data: {user_id: '{{Auth::user()->id ?? null}}', follow_id: follow_id},
                    success: function (data) {
                        if(data.success) {
                            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>'+data.message+'</div>');
                            like_btn.toggleClass('active');
                        } else {
                            $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.error+'</div>');
                        }
                    }
                });
            });



            @if (session('thankToModeration'))
                showCampaignMessagePopup('Ваша благодарность отправлена на модерацию')
            @endif

            $('.product__ava img').magnificPopup({
                type: 'image',
                gallery: {
                    enabled: false
                },
                callbacks: {
                    elementParse: function (qw) {
                        qw.src = qw.el.attr('data-full-src');
                    }
                }
            });

            $('.stories_carousel').slick({
                infinite: true,
                slidesToShow: 1,
                variableWidth: true,
                slidesToScroll: 1,
                prevArrow: $('.story__content-button_back'),
                nextArrow: $('.story__content-button_next'),
                appendDots: $('.story__content-buttons-slider')
            })


            const donateBtn = document.querySelector(".main-content__switch-link_donate");
            const storiesBtn = document.querySelector(".main-content__switch-link_stories");
            const commentsBtn = document.querySelector(
                ".main-content__switch-link_comments"
            );
            const storiesBlock = document.querySelector(".main-content__stories-block");
            const commentBlock = document.querySelector(".main-content__comments-block");
            const donateBlock = document.querySelector(".main-content__donates");
            const btnPopupAdds = document.querySelectorAll(".main-content__donate-button");
            const storyBtn = document.querySelector(".story__clue-icon");

            function swicthToggle(btn, contentBlock) {
                const buttons = [donateBtn, storiesBtn, commentsBtn].filter(Boolean);
                const blocks = [donateBlock, storiesBlock, commentBlock].filter(Boolean);

                buttons.forEach((button) => {
                    button.classList.remove("main-content__switch-link_active");
                });
                blocks.forEach((block) => {
                    block.classList.remove("main-content__block_active");
                });

                btn.classList.add("main-content__switch-link_active");
                contentBlock.classList.add("main-content__block_active");
            }

            if (donateBtn && donateBlock) {
                donateBtn.addEventListener("click", (event) => {
                    event.preventDefault();
                    swicthToggle(donateBtn, donateBlock);
                });
            }

            if (storiesBtn && storiesBlock) {
                storiesBtn.addEventListener("click", (event) => {
                    event.preventDefault();
                    swicthToggle(storiesBtn, storiesBlock);
                });
            }

            if (commentsBtn && commentBlock) {
                commentsBtn.addEventListener("click", (event) => {
                    event.preventDefault();
                    swicthToggle(commentsBtn, commentBlock);
                });
            }

            function openPopup(popupId, classActive) {
                const popup = document.getElementById(popupId);
                popup.classList.add(classActive);
            }

            function closePopup(popupId, classActive) {
                const popup = document.getElementById(popupId);
                popup.classList.remove(classActive);
            }

            function openedAndClosedPopup(button, classActive) {
                button.addEventListener("mouseenter", (event) => {
                    const popupId = event.target.dataset.popup;
                    openPopup(popupId, classActive);
                });

                button.addEventListener("mouseleave", (event) => {
                    const popupId = event.target.dataset.popup;
                    closePopup(popupId, classActive);
                });
            }

            if(storyBtn) {
                openedAndClosedPopup(storyBtn, "story__clue-popup_active");
            }


            function togglePopup(popupId, classActive) {
                const popup = document.getElementById(popupId);
                const isOpen = popup.classList.contains(classActive);

                if (isOpen) {
                    popup.classList.remove(classActive);
                } else {
                    popup.classList.add(classActive);
                }
            }

            function handlePopupButtonClick(event) {
                const popupId = event.target.dataset.popup;
                const classActive = "main-content__donate-pop-up_active";
                togglePopup(popupId, classActive);
            }

            btnPopupAdds.forEach((button) => {
                button.addEventListener("click", handlePopupButtonClick);
            });


        })

        $(document).ready(function () {
            @if(isset($_GET['thanks']) && $_GET['thanks'])

                var $scrollTo = $('.main-content__donate-button[data-popup="popup-{{$_GET['thanks']}}"]');
                var $thanksBtn = $('#popup-{{$_GET['thanks']}}.main-content__donate-pop-up .main-content__donate-pop-up-button_text');
                $('html,body').animate({
                    scrollTop: ($scrollTo.offset().top - 200)
                }, 300);
                $scrollTo.trigger('click');
                $thanksBtn.trigger('click');
                // $scrollTo.parents('.main-content__donate').addClass('active');
            @endif
        });

        let currentStoryIndex = 0;
        let carouselItems = []; // Will store the references to the carousel items

        function initializeCarouselItems() {
            carouselItems = $('.stories_carousel .show_story').toArray(); // Get all carousel items
        }

        $('body').on('click', '.show_campaign_story', function (e) {

            initializeCarouselItems(); // Ensure carousel items are up-to-date
            currentStoryIndex = carouselItems.indexOf(this); // Get the index of the clicked item
            if($('body').find('.modal_controls').length == 0) {
                $('#story-popup').addClass('with_controls');
                var controls = '<div class="modal_controls"><button id="prev-story" class="story__content-button story__content-button_back slick-arrow" style=""></button><button id="next-story" class="story__content-button story__content-button_next slick-arrow" style=""></button></div>';
                $('#story-popup').append(controls);
            }

        });

        $('body').on('click', '#next-story', function () {
            showStoryByIndex(currentStoryIndex + 1);
        });

        $('body').on('click', '#prev-story', function () {
            showStoryByIndex(currentStoryIndex - 1);
        });


        function showStoryByIndex(index) {
            if (index < 0 || index >= carouselItems.length) {
                return; // Out of bounds
            }
            currentStoryIndex = index;

            const story = $(carouselItems[currentStoryIndex]);
            const route = story.attr('data-route');

            // Fetch and display the story
            $.ajax({
                type: 'GET',
                url: route,
                success: function (data) {
                    if (data.success) {
                        showStory(data.data);
                    } else {
                        console.log('asdsa');
                    }
                }
            });
        }
    </script>

    @if (request('success_pay'))
        <script>
            showCampaignMessagePopup('Ваш платеж успешно внесен! Скоро он отобразится в копилке')
        </script>
    @endif

    @if (session('wake_message'))
        <script>
            showCampaignMessagePopup(@json(session('wake_message')))
        </script>
    @endif

{{--    @include('dashboard.stories.stories_scripts')--}}

@endsection

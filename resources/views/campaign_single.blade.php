@extends('layouts.neon.app')
@section('title')
    @if( ! empty($title))
        {{ $title }} | {{$campaign->id}}
    @endif @parent
@endsection

@section('meta-data')
    <meta name="description" content="{{$campaign->short_description ?: $campaign->description}}"/>

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="{{$campaign->short_description ?  : $campaign->description}}">
    {{--<meta name="twitter:site" content="@publisher_handle">--}}
    <meta name="twitter:title" content="@if( ! empty($title)) {{ $title }} @endif">
    <meta name="twitter:description"
          content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}">
    {{--<meta name="twitter:creator" content="@author_handle" />--}}
    <!-- Twitter Summary card images must be at least 120x120px -->
    <meta name="twitter:image" content="{!! $campaign->feature_img_url()->feature_image !!}">

    <!-- Open Graph data -->
    <meta property="og:title" content="@if( ! empty($title)) {{ $title }} @endif"/>
    <meta property="og:url" content="{{route('campaign_single', $campaign->slug)}}"/>
    <meta property="og:image" content="{{$campaign->feature_img_url()->feature_image}}"/>
    <meta property="og:type" content="article"/>
    <meta property="og:description"
          content="{{$campaign->short_description ?: $campaign->description}}"/>

@endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    
@endsection

@section('content')

    <div class="product">
    
        <div class="container">
	
            <div class="product__main">
                <div class="product__info">
                    <div class="product__info-photo product__desk">
                        <div class="slider-for">
			
                            @if ($campaign->images)
                                @foreach($campaign->images as $image)
				
                                    <div><img src="{{media_image_uri($image)->original}}" alt=""></div>
                                @endforeach
                            @else
                                <div class="campaign_image_container"
				
                                     style=" background-image: url('{{$campaign->feature_img_url()->original}}')">
                                    @if ((int)$campaign->percent_raised() >= 100)
				    
                                        @include('inc.fully_banner')
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="slider-nav">
                            @if ($campaign->images)
                                @foreach($campaign->images as $image)
				
                                    <div class="product__slide">
                                        <img class="product__img" src="{{media_image_uri($image)->original}}" alt="">
                                    </div>
				    
                                @endforeach
                            @else
                                <div class="product__slide">
                                    <img class="product__img" src="{{$campaign->feature_img_url()->original}}" alt="">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="product__slider product__mobile">
                        @if ((int)$campaign->percent_raised() >= 100)
                            @include('inc.fully_banner')
                        @endif
                        <div class="owl-carousel owl-theme product__carousel">
                            @if ($campaign->images)
                                @foreach($campaign->images as $image)
                                    <img src="{{media_image_uri($image)->original}}" alt="">
                                @endforeach
                            @else
                                <img src="{{$campaign->feature_img_url()->original}}" alt="">
                            @endif
                        </div>
                    </div>
                    <div class="product__info-content">
                        <div class="product__category flex">
                            <div>Категория - {{$campaign->get_category->category_name}}</div>
                            <div>№ {{$campaign->id}}</div>
                        </div>
                        <h2 style="text-align: left"> {!! $campaign->title !!}
                            <span>
                                         {!! $campaign->title !!}
                                    </span>
                        </h2>
                        <div class="product__statistics">
                            <div class="product__statistics-title">Статистика копилки</div>
                            <div class="product__statistics-info">
                                @if ((int)$campaign->percent_raised() < 100)
                                    <div class="product__progressbar-text product__progressbar-to">100%
                                        <span>100%</span>
                                    </div>
                                @endif

                                @php
                                    $percent_raised = $campaign->percent_raised();
                                @endphp

                                <div class="product__progressbar">
                                    <span class="product__progressbar-span"
                                          style="width: {!! $percent_raised <= 100 ? $percent_raised : 100 !!}%"><div
                                                class="product__progressbar-text product__progressbar-from">{!! $percent_raised !!}% <span>{!! $percent_raised !!}%</span></div></span>
                                </div>
                            </div>
                        </div>
                        <div class="product__items">
                            <div class="product__item flex">
                                <span>Цель</span><span>{!! get_amount($campaign->goal) !!}</span>
                            </div>
                            <div class="product__item flex"><span>Осталось дней</span><span>∞</span></div>
                            <div class="product__item flex">
                                <span>Спонсоры</span><span>{!! $campaign->success_payments->count() !!}</span></div>
                            <div class="product__item flex">
                                <span>Финансировано</span><span>
                                    @if (in_array($campaign->slug, [
            'onlain-kurs-po-fotopozirovaniyu',
            'alisa',
            'elektrouselitel-rulya-dlya-sestyorki-3']))
                                        {!! get_amount($campaign->goal) !!}
                                    @else
                                        {!! get_amount($campaign->success_payments->sum('amount')) !!}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="product__btns">
                            @if ((int)$campaign->percent_raised() < 100)
                                <div class="product__btn btn btn_fill product__btn-pay">Внести донат</div>
                                <div class="product__btn product__btn-dark product__btn-pay_auto">Подписка на автоплатеж
                                </div>
                            @endif
                            <div class="product__flex flex">
                                <div class="product__btn product__btn-dark flex btn__copy">Скопировать
                                    <img src="/dist/images/icons/clip.svg"></div>
                                <div class="product__btn product__btn-dark product__btn-heart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product__icons">
                    <div class="product__icon">
                        <a target="_blank" href="" data-share-vk><img src="/dist/images/icons/vk_color.svg"></a>
                    </div>
                    {{--                    <div class="product__icon">--}}
                    {{--                        <a target="_blank" href="https://www.instagram.com" data-share-instagramm>--}}
                    {{--                            <img src="/dist/images/icons/inctagram_color.svg">--}}
                    {{--                        </a>--}}
                    {{--                    </div>--}}
                    {{--                    <div class="product__icon">--}}
                    {{--                        <a target="_blank" href="" data-share-facebook><img src="/dist/images/icons/facebook_color.svg"></a>--}}
                    {{--                    </div>--}}
                    <div class="product__icon">
                        <a target="_blank" href="" data-share-twitter><img
                                    src="/dist/images/icons/twitter_color.svg"></a>
                    </div>
                </div>
            </div>
            <div class="product__description">
                <div class="product__user">
                    <div class="product__ava">
                        <img src="{{ $campaign->user?->avatar() }}" data-full-src="{{$campaign->user?->avatar(true)}}">
                    </div>
                    <span>{!! $campaign->user->fullname !!}</span>
                </div>
                <div class="product__description-title">Описание
                    {{--                    <span>{!! $campaign->short_description !!}</span>--}}
                </div>
                <span style="display:block; overflow:hidden; font-size: 22px">{!! safe_output($campaign->description) !!}</span>
            </div>
        </div>
    </div>

    @php
        $comments = \App\Models\Comment::approved()->parent()->whereCampaignId($campaign->id)->with('childs_approved')->orderBy('id', 'desc')->get();
            $comments_count = \App\Models\Comment::approved()->whereCampaignId($campaign->id)->count();
    @endphp

    <div class="moneybox-donators container">
        <h2 class="moneybox-subtitle text-shadow">Донатеры</h2>
        @foreach($campaign->success_payments as $payment)
            @if($payment->user)
                <div class="moneybox-donator">
                    <div class="moneybox-donator__head">
                        <div class="moneybox-donator__user">
                            <img class="moneybox-donator__avatar magnific_image circle-img" src="{{$payment->user->avatar()}}" alt="">
                            <div class="moneybox-donator__content">
                                <div class="moneybox-donator__name">{{$payment->user->fullname}}</div>
                                <div class="moneybox-donator__date">{{$payment->created_at->format('d.m.Y')}}</div>
                            </div>
                        </div>
                        <div class="moneybox-donator__side">
                            <div class="moneybox-donator__cost">{{$payment->amount}}₽</div>
                            <a class="moneybox-donator__btn" href="#comment_box_camp">Поблагодарить</a>
                        </div>
                    </div>
                    <div class="moneybox-donator__body">
                        @if(!$payment->comment && $campaign->user->id === auth()->id())
                            <form class="moneybox-comment__form" action="{{route('payment_comment')}}" method="post">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{$payment->id}}">
                                <textarea name="text" rows="10" required id="comment_box_camp"></textarea>
                                <button type="submit">Отправить</button>
                            </form>
                        @endif
                    </div>

                    @if($payment->comment)
                        <div class="moneybox-comment">
                            <div class="moneybox-comment__head">
                                <div class="moneybox-donator__date">
                                    {{$payment->comment->created_at}}</div>
                            </div>
                            <div class="moneybox-comment__body">
                                <p class="moneybox-comment__text" style="overflow-wrap: break-word;">
                                    {{$payment->comment->text}}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    <div class="comments">
        <div class="container">
            @if($comments_count < 1)
                <div class="comments__info">Нет комментариев, будь первым
                    <span>Нет комментариев, будь первым</span>
                </div>
            @else
                <div class="comments__items">
                    @foreach($comments as $comment)
                        <div id="comment-{{$comment->id}}" class="comments__item comment flex">
                            <div class="comment__photo">
                                @if($comment->user_id)
                                    <img src="{{$comment->author?->avatar()}}" class="magnific_image circle-img" alt="{{$comment->author_name}}" data-image="{{$comment->author?->avatar()}}">
                                @else
                                    <img src="{{avatar_by_email($comment->author_email)}}"
                                         alt="{{$comment->author_name}}"
                                         class="magnific_image circle-img"
                                         data-image="{{avatar_by_email($comment->author_email)}}"
                                    >
                                @endif
                            </div>
                            <div class="comment__info">
                                <div class="comment__name">{{$comment->author_name}}
                                    <span>{{$comment->author_name}}</span>
                                </div>
                                <div class="comment__text">
                                    {!! safe_output(nl2br($comment->comment)) !!}
                                </div>
                                {{--                                <div class="comment__score">--}}
                                {{--                                    <div class="comment__like"><img src="/dist/images/icons/like.svg"><span>7</span></div>--}}
                                {{--                                    <div class="comment__dislike"><img src="/dist/images/icons/dislike.svg"><span>1</span></div>--}}
                                {{--                                </div>--}}
                            </div>
                        </div>
                    @endforeach
                </div>

            @endif
            <form action="{{route('post_comments', $campaign->id)}}" method="post" class="comments__form">
                @csrf
                <label for="comment">Ваш комментарий</label>
                <textarea name="comment" id="comment" cols="30" rows="10" placeholder="Введите текст..."></textarea>
                <div class="comments__send flex">
                    <div class="comments__btn btn btn_fill" onclick="$(this).parents('form').submit()">Отправить
                    </div>
                    <div class="comments__agree">
                        <input class="comments__checkbox" id="checkbox" type="checkbox">
                        <label for="checkbox">Даю согласие на обработку своих персональных данных. <br>
                            С Политикой конфиденциальности ознакомлен и согласен.</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-js')
    @if(request('s')==='y')
        <script>
			alert('Успех! Ваш комментарий появится сразу после прохождения модерации');
        </script>
    @endif

    <div class="popup__wrape_1">
        <div class="popup__modal">
            <form action="{{route('add_to_cart')}}" class="campaign__donat-form">
                <input type="hidden" name="campaign_id" value="{!! $campaign->id !!}"/>
                <div class="popup__close"><img src="/dist/images/icons/close.svg" alt=""></div>
                <div class="popup__title">Введите сумму доната</div>
                <input id="input" name="amount" required value="50" min="50" type="number" class="popup__input"
                       placeholder="50.00₽">
                <button class="popup__btn btn btn_fill" type="submit">Подтвердить</button>
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
                <input id="input" name="amount" required value="50" min="50" type="number" class="popup__input"
                       placeholder="50.00₽">
                <button class="popup__btn btn btn_fill" type="submit">Подтвердить</button>
            </form>
        </div>
    </div>

    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <script>
		$(function () {
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
		})
    </script>

    @if (request('success_pay'))
        <script>
			alert('Ваш платеж успешно внесен! Скоро он отобразится в копилке')
        </script>
    @endif

@endsection

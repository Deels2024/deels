@extends('layouts.neon.app')
@section('title')
    @if(!empty($title))
        {{ $title }}
    @endif
@endsection
@if(!empty($description))
    @push('meta-data')
        <meta name="description" content="{{ $description }}">
    @endpush
@endif

@section('content')
    <div class="background">
        <div class="background__filter">

            <div style="display:block; padding-bottom:70px; margin-bottom: 70px"></div>
            @if(isset($topChallenges) && count($topChallenges) > 0)
            <div class="ch-block">
                <div class="container">
                    <div class="ch-block-wrap">
                        <div class="ch-block-title text-center d-block d-lg-none">Топ челленджей</div>
                        <div class="ch-block-row">
                            <a href="{{route('challenges.create')}}" class="ch-block-side">
                                <img src="{{ ext_asset('img/ch-block-image.png') }}" width="470" height="713" alt="">
                            </a>
                            <div class="ch-block-main">
                                <div class="ch-block-main__top d-none d-lg-flex">
                                    <h1 class="ch-block-title text-uppercase">Топ челленджей</h1>
                                    <div class="d-none d-lg-block">
                                        <a href="{{route('challenges.catalog')}}" class="ch-block__btn ch-block__btn--xs ch-block__btn--outline">Смотреть все</a>
                                        <a href="{{route('challenges.create')}}" class="ch-block__btn ch-block__btn--xs ch-block__btn--fill">Создать свой</a>
                                    </div>
                                </div>
                                <a href="{{route('challenges.create')}}" class="ch-block__btn ch-block__btn--xl ch-block__btn--fill text-uppercase d-flex d-md-none">Создать свой челлендж</a>

                                <div class="ch-block-slider owl-carousel owl-theme">

                                    @foreach($topChallenges as $challenge)
                                        @include('challenges.challenge_item', ['route' => route('challenge_page', $challenge->id)])
                                    @endforeach
                                </div>
                                <div class="d-flex d-lg-none gap-3">
                                    <a href="{{route('challenges.create')}}" class="ch-block__btn ch-block__btn--xl ch-block__btn--fill text-uppercase d-none d-md-flex">Создать свой челлендж</a>
                                    <a href="{{route('challenges.catalog')}}" class="ch-block__btn ch-block__btn--xl ch-block__btn--outline">Смотреть все челенджи</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="showcase">
                <div class="wrapper-container">
                    <div class="showcase__start">
                        <div class="showcase__start-info">
                            <h2 class="showcase__start-title">
                                Начни уже сегодня
                            </h2>
                            <div class="showcase__start-mob">
                                <img alt="" src="/images/action-top-banner/startmob.webp"></div>
                            <div class="buttons-row buttons-row-small mb-5">
                                <a href="{{route('stories.create')}}" class="hero-btn">Создать сторис</a>
                                <a href="{{route('start_campaign')}}" class="hero-btn hero-btn-dark"  onclick="window.location='{{route('start_campaign')}}'">Начать копить</a>

                            </div>
                            <div class="showcase__start-follow">
                                <div class="showcase__start-icon">
                                    <a href="https://t.me/deels_ru" target="_blank">
                                        <img alt="" src="/images/action-top-banner/tg.svg">
                                    </a>
                                    <a href="https://vk.com/deels" target="_blank">
                                        <img alt="" src="/images/action-top-banner/vk.svg">
                                    </a>
                                </div>
                                <div class="showcase__start-message">
                                    Подпишись, чтобы не пропустить последние новости!
                                </div>
                            </div>
                            <a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank"><img src="/images/promo/android.png" class="app_image"></a>
                            <a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank"><img src="/images/promo/appstore.png" class="app_image"></a>
                        </div>
                        <div class="showcase__start-img">
                            <div class="main_promo_slider owl-carousel owl-theme">
                                <div class="item"><img alt="" src="/images/promo/banner1.png"></div>
                                <div class="item"><img alt="" src="/images/promo/banner2.png"></div>
                                <div class="item"><img alt="" src="/images/promo/banner3.png"></div>
                                <div class="item"><img alt="" src="/images/promo/banner4.png"></div>
                                <div class="item"><img alt="" src="/images/promo/banner5.png"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript" src="/dist/js/number-flip.js"></script>

            <div class="section bcounter">
                <div class="container">
                    <h2 class="bcounter__title">
                        КОИНЫ
                        <b>DEELS</b>
                    </h2>
                    <small style="display: block; text-align: center; position: relative; top: -30px">Виртуальная внутренняя единица платформы</small>
                    <div class="d-flex">
                        <div class="bcounter-wrap">
                            @php
                                $deels_bank_user = \App\Models\User::where('email', 'moderdeels@mail.ru')->first();
                                $transactions_total = \Bavix\Wallet\Models\Transaction::where('meta', 'like', '%"get":"coins","old_connected"%')->sum('amount');
                                if($deels_bank_user) {
                                    $deels_wallet_balance = intval($deels_bank_user->wallet_balance ?? 0);
//                                    $bank = intval($deels_wallet_balance-intval($transactions_total));
                                    $bank = $deels_wallet_balance;
                                    if($bank < 0) {
                                        $bank = 0;
                                    }
                                } else {
                                    $bank = intval(10000000-$transactions_total);
                                }

                            @endphp
                               <div class="numCounter" data-value="{{$bank}}"><div>
                                       <b data-value="0"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="0"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="1"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="2"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="3"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="4" class="blur"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="5" class="blur"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div><div><b data-value="6" class="blur"><span>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br></span></b></div></div>
                           </div>
                       </div>
                   </div>
               </div>

               <script>
                   var counter = new Counter('.numCounter', {
                       direction : 'rtl',
                       delay     : 100,
                       digits    : 8
                   })
               </script>
            @push('after_scripts')
              <script>
                  function get_bank() {
                      $.ajax({
                          type: 'GET',
                          url: '{{ route('coins_bank') }}',
                          success: function (data) {
                              counter.count(data.count);
                              setTimeout(function() {
                                  get_bank();
                              }, 10000);
                          }
                      });
                  }
                  setTimeout(function() {
                      get_bank();
                  }, 10000);

              </script>
            @endpush

               @if(count($topStories))
                   <!-- tops-section -->
                   <section class="tops">
                       <div class="container">
                           <h2 class="tops-title"><a href="{{route('stories.catalog',['type' => 'popular'])}}">Топ сторис</a></h2>
                           <div class="index-carousel owl-carousel owl-theme">
                               @foreach($topStories as $story)
                                   @include('stories.story_item', ['story' => $story])
                               @endforeach
                           </div>
                       </div>
                   </section>
               @endif

               @if(count($donateStories))
                   <section class="tops">
                       <div class="container">
                           <h2 class="tops-title"><a href="{{route('stories.catalog',['type' => 'paid'])}}">Сторис за донаты</a></h2>
                           <div class="index-carousel owl-carousel owl-theme">
                               @foreach($donateStories as $story)
                                   @include('stories.story_item', ['story' => $story])
                               @endforeach
                           </div>
                       </div>
                   </section>
               @endif

               @if(count($newStories))
                   <section class="tops">
                       <div class="container">
                           <h2 class="tops-title"><a href="{{route('stories.catalog',['type' => 'new'])}}">Новинки</a></h2>
                           <div class="index-carousel owl-carousel owl-theme">
                               @foreach($newStories as $story)
                                   @include('stories.story_item', ['story' => $story])
                               @endforeach
                           </div>
                       </div>
                   </section>
               @endif
               <!-- End tops-section -->

               <!-- info  -->
            @include('partials.home.stats')
               <!-- End info -->

               <!-- finish -->
               <div class="finish">
                   <div class="container">
                       <h2 class="finish__title">Поздравляем с<br>
                           осуществлением мечты! <span>Поздравляем с<br>
                       осуществлением мечты!</span></h2>

                       <div class="index-carousel owl-carousel owl-theme">
                           @foreach($completedCampaigns as $campaign)
                               <a href="{{route('campaign_single', $campaign->slug)}}" class="finish-item">
                                   <div class="finish-item__img">
                                       <img src="{{ $campaign->feature_img_url()->thumbnail ?? $campaign->feature_img_url()->feature_image }}" alt="">
                                       <span>100%</span>
                                   </div>
                                   @if(isset($campaign->user))
                                   <div class="finish-item__head">
                                       <img src="{{$campaign->user->avatar()}}" alt="" width="25" height="25">
                                       <span>{{$campaign->user->fullname}}</span>
                                   </div>
                                   @endif
                                   <div class="finish-item__name">{{$campaign->title}}</div>
                                   <ul class="finish-item__content">
                                       <li>
                                           <span>Осталось дней:</span>
                                           <span class="text-accent">∞</span>
                                       </li>
                                       <li>
                                           <span>Спонсоры: </span>
                                           <span class="text-accent">{!! $campaign->success_payments->count() !!}</span>
                                       </li>
                                       <li>
                                           <span>Финансировано:</span>
                                           <span class="text-accent">{!! get_amount($campaign->goal) !!}</span>
                                       </li>
                                   </ul>
                               </a>
                           @endforeach
                       </div>
                   </div>
               </div>
               <!-- End finish -->

               <div class="container">
                   <div class="bank bank_index">
                       <h2>
                           <a href="/campaigns">Финансируемые копилки</a>
                       </h2>
                       <div class="owl-carousel owl-theme bank__carousel">

                           @foreach($fundedCampaigns as $campaign)
                               @include('campaigns.campaign_card', ['campaign' => $campaign])
                           @endforeach
                       </div>
                   </div>

                   <div class="bank bank_index">
                       <h2>
                           <a href="/campaigns">Недавно пополненные копилки</a>
                       </h2>
                       <div class="owl-carousel owl-theme bank__carousel">
                           @foreach($latestFundedCampaigns as $campaign)
                               @include('campaigns.campaign_card', ['campaign' => $campaign])
                           @endforeach
                       </div>
                   </div>

                   <div class="bank bank_index">
                       <h2>
                           <a href="/campaigns?type=new">Новые копилки</a>
                       </h2>
                       <div class="owl-carousel owl-theme bank__carousel">
                           @foreach($newCampaigns as $campaign)
                               @php
                                   $percent_raised = $campaign->percent_raised();
                            @endphp
                               @include('campaigns.campaign_card', ['campaign' => $campaign])
                        @endforeach
                    </div>
                </div>
            </div>

            @include('partials.home.whydeels')

            @include('partials.home.deels_info')

            @include('partials.home.bottom_start')



            @if(get_option('ad_popup_active'))
                <a href="#promo_popup" class="hero-list__item popup_block" style="width: 0;height: 0;position: absolute;bottom: 0;z-index: -1"></a>
            @endif
            <a href="#mobile_app_popup" class="hero-list__item popup_block" style="width: 0;height: 0;position: absolute;bottom: 0;z-index: -1"></a>
        </div>
    </div>





    @include('stories.modal')
<style>
    .demo-video video {
        height: auto!important;
    }
</style>
    <div class="story story--media demo-video mfp-hide" id="hero-video-popup">
        <div class="story-wrap">
            <div class="story-media">
                <video src=""></video>
            </div>
        </div>
    </div>

    <style>
        .promo_popup_block {
            max-width: 80%;
            max-height: 710px;
        }
        .promo_popup_block img{
            position: relative;
        }
        .promo_popup_block .mfp-close {
            z-index: 3;
        }
        .promo_popup_block .story-media {
            text-align: center;
            background: transparent!important;
        }
    </style>
    @if(get_option('ad_popup_active', true))

        <div class="story story--media demo-video mfp-hide promo_popup_block" id="promo_popup">
            <div class="story-wrap">
                <div class="story-media">
                    <a href="{{get_option('ad_popup_url', true)}}"><img src="{{get_option('ad_popup_image', true)}}"></a>
                </div>
            </div>
        </div>
    @endif

    <style>
        .mobile_app_popup {
            h5 {
                margin-bottom: 20px!important;
                display: block!important;
            }
            .app_image {
                height: 40px!important;
                width: auto!important;
                margin: 5px!important;
            }
            .modal_close {
                display: block!important;
                margin-top: 20px!important;
                text-decoration: underline!important;
                opacity: .6!important;
            }
            .mobile_content {
                margin-top: 100px!important;
                border: 2px solid #fff!important;
                border-radius: 10px!important;
                padding: 20px!important;
                background-color: #0D102C!important;
                -webkit-box-shadow: 0 0 8px rgba(255, 255, 255, .5), inset 0 0 8px rgba(255, 255, 255, .5)!important;
                box-shadow: 0 0 8px rgba(255, 255, 255, .5), inset 0 0 8px rgba(255, 255, 255, .5)!important;
            }
        }
    </style>
    <div class="story story--media demo-video mfp-hide promo_popup_block mobile_app_popup" id="mobile_app_popup">
        <div class="story-wrap">
            <div class="story-media">
                <div class="mobile_content">
                    <h5>Скачайте наше приложение:</h5>
                    <a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank"><img src="/images/promo/android.png" class="app_image"></a>
                    <a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank"><img src="/images/promo/appstore.png" class="app_image"></a>
                    <span class="modal_close">Остаться в браузере</span>
                </div>

            </div>
        </div>
    </div>



@endsection

@section('page-js')
    <script type="text/javascript" src="{{ asset('/js/libs/jquery-cookies/jquery-cookies.js') }}"></script>
    <script>
        @if(!\Cookie::get('mobile_app'))
        $( document ).ready(function() {
            if ($(window).width() < 767) {
                $.cookie('mobile_app', true, {expires: 14, path: '/'});
                $('.modal_close').click(() => {
                    $.magnificPopup.close({
                        items: {
                            src: '#mobile_app_popup'
                        },
                        type: 'inline'
                    });
                });
                searchTimer = setTimeout(function () {
                    $.magnificPopup.open({
                        items: {
                            src: '#mobile_app_popup'
                        },
                        type: 'inline'
                    });
                }, 1000);
            }
        });
        @endif

        @if(get_option('ad_popup_active', true) && get_option('ad_popup_image', true) && \Cookie::get('mobile_app') && !\Cookie::get('promo_block'))
            $( document ).ready(function() {
                searchTimer = setTimeout(function() {
                    $.magnificPopup.open({
                        items: {
                            src: '#promo_popup'
                        },
                        type: 'inline'
                    });
                    $.cookie('promo_block', true, { expires: 14, path: '/' });
                    var popup_height = $('.promo_popup_block').height();
                    var image_height = $('.promo_popup_block img').height();
                    var image_width = $('.promo_popup_block img').width();
                    if(image_height > image_width || image_height > popup_height) {
                        $('.promo_popup_block img').height(popup_height);
                    }

                    searchTimer = setTimeout(function() {
                        $.magnificPopup.close({
                            items: {
                                src: '#promo_popup'
                            },
                            type: 'inline'
                        });
                    }, 5000);

                }, 3000);

            });
        @endif
        $('[data-video-link]').magnificPopup({
            type : 'inline',
            callbacks : {
                open : function() {
                    var mp = $.magnificPopup.instance,
                        t = $(mp.currItem.el[0]),
                        videoUrl = t[0].dataset.videoLink,
                        thVideo = this.content.find('video');
                    thVideo.attr('src', videoUrl)
                    thVideo[0].play()
                },
                close: function() {
                    var thVideo = this.content.find('video');
                    thVideo[0].pause();
                    thVideo[0].currentTime = 0;
                }
            }
        });

        $('.popup_block').magnificPopup({
            type : 'inline',
            callbacks : {
                open : function() {
                    var mp = $.magnificPopup.instance,
                        t = $(mp.currItem.el[0]),
                        videoUrl = t[0].dataset.videoLink,
                        thVideo = this.content.find('img');
                    thVideo.attr('src', videoUrl);
                },
                close: function() {

                }
            }
        });

        $( document ).ready(function() {
            $('.ch-block-slider').hide();
            $('.ch-block-slider').owlCarousel({
                margin: 20,
                loop: false,
                dots: false,
                nav: true,
                autoWidth:true,
                responsive:{
                    0:{
                        margin: 10,
                        dots: true,
                        nav: false
                    },
                    981:{
                        dots: false,
                        nav: true
                    }
                },
                navText: [
                    '<svg width="16" height="34" viewBox="0 0 16 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 32.5L1.70148 19.0601C1.4869 18.8459 1.30952 18.5436 1.1866 18.1826C1.06368 17.8216 0.999397 17.4142 1.00001 17C0.999397 16.5858 1.06368 16.1784 1.1866 15.8174C1.30952 15.4564 1.4869 15.1541 1.70148 14.9399L15 1.5" stroke="#00F0FF" stroke-width="2"/></svg>',
                    '<svg width="16" height="34" viewBox="0 0 16 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 32.5L14.2985 19.0601C14.5131 18.8459 14.6905 18.5436 14.8134 18.1826C14.9363 17.8216 15.0006 17.4142 15 17C15.0006 16.5858 14.9363 16.1784 14.8134 15.8174C14.6905 15.4564 14.5131 15.1541 14.2985 14.9399L1 1.5" stroke="#00F0FF" stroke-width="2"/></svg>'
                ],
                onInitialized: function() {
                    $('.ch-block-slider').show();
                }
            });

            $('.index-carousel').hide();
            $('.index-carousel').owlCarousel({
                margin: 20,
                loop: false,
                dots: true,
                nav: true,
                autoWidth:true,
                navText: [
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M15 32L1.70148 18.5601C1.4869 18.3459 1.30952 18.0436 1.1866 17.6826C1.06368 17.3216 0.999397 16.9142 1.00001 16.5C0.999397 16.0858 1.06368 15.6784 1.1866 15.3174C1.30952 14.9564 1.4869 14.6541 1.70148 14.4399L15 1" stroke="#00F0FF"/></svg>',
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M1 32L14.2985 18.5601C14.5131 18.3459 14.6905 18.0436 14.8134 17.6826C14.9363 17.3216 15.0006 16.9142 15 16.5C15.0006 16.0858 14.9363 15.6784 14.8134 15.3174C14.6905 14.9564 14.5131 14.6541 14.2985 14.4399L1 1" stroke="#00F0FF"/></svg>'
                ],
                onInitialized: function() {
                    $('.index-carousel').show();
                }
            });


            //donators-slider
            $('.donators-slider').hide();
            $('.donators-slider').owlCarousel({
                loop: true,
                nav: true,
                dots: false,
                center: true,
                margin: 20,
                stagePadding: 10,
                navText: [
                    '<svg viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg"><circle r="21.5" transform="matrix(-1 0 0 1 21.5 21.5)"/><path d="M24 15L18 21.5L24 28" stroke-width="2"/></svg>',
                    '<svg viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="21.5" cy="21.5" r="21.5" /><path d="M19 15L25 21.5L19 28" stroke-width="2"/></svg>'],
                items: 1,
                responsive: {
                    0: {
                        margin: 4,
                    },
                    480: {
                        margin: 20,
                    },
                },
                onInitialized: function() {
                    $('.donators-slider').show();
                }
            });

        });

		if ($(window).width() >= 900) {
			$('.topslider').slick({
				arrows: false,
				slidesToShow: 1,
				infinite: true,
				autoplay: true,
				autoplaySpeed: 5000,
			})
		}

		$('.finish-slider__track').slick({
			arrows: false,
			slidesToShow: 3,
			infinite: false,

			prevArrow: $('.finish-slider__prev'),
			nextArrow: $('.finish-slider__next'),

			responsive: [
				{
					breakpoint: 980,
					settings: {
						slidesToShow: 2,
					}
				},
				{
					breakpoint: 650,
					settings: {
						slidesToShow: 1,
					}
				},
			]
		});

		$('.finish-slider__prev').click(() => {
			$('.finish-slider__track').slick('slickPrev')
		});
		$('.finish-slider__next').click(() => {
			$('.finish-slider__track').slick('slickNext')
		});

        $( document ).ready(function() {
            function resize_window() {
                $(window).trigger('resize');
                setTimeout(function() {
                    resize_window();
                }, 3000);
            }
            setTimeout(function() {
                resize_window();
            }, 3000);
        });

    </script>
    <script src="/dist/js/swiper.min.js"></script>
    <script src="/js/libs/fancybox/jquery.fancybox.min.js"></script>
    <script src="/dist/js/actions-top.js"></script>
{{--    @include('dashboard.stories.stories_scripts')--}}
@endsection
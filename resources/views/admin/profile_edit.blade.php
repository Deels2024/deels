@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')

    <main class="account-main">
        <div class="account-main__head">
            <h1 class="account-main__title">Мой профиль</h1>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <div class="account-info">
            <div class="row account-info__row">
                <div class="account-info__profile row">
                    <div class="profile-avatar avatar avatar--lg" style="border-radius: 50%; overflow: hidden">
                        <img src="{{Auth::user()->avatar()}}" alt="" width="130" height="130"></div>
                    <div class="row flex-direction-column-direction">
                        <div class="profile-info">
                            <div class="profile-info__name">{{$user->fullname}}</div>
                            <a class="profile-info__email" href="mailto:{{$user->email}}">{{$user->email}}</a>
                        </div>
                        <br>
                        <a class="profile-dignity__link row ai-center dignity-popup-link" href="#profile_picture">Изменить
                            фото профиля<span>&#8250;</span></a>
                    </div>
                </div>
                <div class="account-info__dignity">
                    <div class="profile-dignity bg-dark">
                        <div class="profile-dignity__badge">
                            <svg width="141" height="153" viewBox="0 0 141 153" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g filter="url(#filter0_f_30_443)">
                                    <path d="M107 24H34C28.4772 24 24 28.4772 24 34V95.8076C24 99.2809 25.8022 102.505 28.7606 104.325L65.2606 126.777C68.4739 128.754 72.5261 128.754 75.7393 126.777L112.239 104.325C115.198 102.505 117 99.2809 117 95.8076V34C117 28.4772 112.523 24 107 24Z"
                                          fill="url(#paint0_linear_30_443)"/>
                                </g>
                                <path d="M107 24H34C28.4772 24 24 28.4772 24 34V95.8076C24 99.2809 25.8022 102.505 28.7606 104.325L65.2606 126.777C68.4739 128.754 72.5261 128.754 75.7393 126.777L112.239 104.325C115.198 102.505 117 99.2809 117 95.8076V34C117 28.4772 112.523 24 107 24Z"
                                      fill="url(#paint1_linear_30_443)"/>
                                <path d="M41 34H100C103.866 34 107 37.134 107 41V92.0937C107 94.5054 105.759 96.7472 103.714 98.0269L74.2144 116.495C71.9424 117.917 69.0576 117.917 66.7856 116.495L37.2856 98.0269C35.2414 96.7472 34 94.5054 34 92.0937V41C34 37.134 37.134 34 41 34Z"
                                      stroke="white" stroke-opacity="0.2" stroke-width="2"/>
                                <path d="M59.9548 67.0991L56.6417 55.3886C56.4014 54.5392 57.2991 53.8178 58.0769 54.2353L62.5606 56.642C62.9656 56.8594 63.4666 56.7727 63.775 56.4319L69.7334 49.8472C70.1393 49.3986 70.8473 49.4103 71.2382 49.872L76.7728 56.4102C77.0716 56.7632 77.5734 56.8652 77.9863 56.657L82.7992 54.2302C83.601 53.8259 84.488 54.6013 84.1945 55.4499L80.1436 67.165C79.9787 67.6419 79.4869 67.9227 78.9915 67.8269C77.1933 67.4791 72.9657 66.729 69.9761 66.7333C67.0538 66.7376 62.9362 67.4662 61.1252 67.8163C60.608 67.9163 60.0982 67.6059 59.9548 67.0991Z"
                                      stroke="white" stroke-width="3" stroke-linecap="round"/>
                                <foreignobject x="55" y="74" width="40" height="30">
                                    <body xmlns="http://www.w3.org/1999/xhtml">
                                    <span>№{{$currentPosition}}</span>
                                    </body>
                                </foreignobject>
                                <defs>
                                    <filter id="filter0_f_30_443" x="0" y="0" width="141" height="152.26"
                                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix"
                                                 result="shape"/>
                                        <feGaussianBlur stdDeviation="12" result="effect1_foregroundBlur_30_443"/>
                                    </filter>
                                    <linearGradient id="paint0_linear_30_443" x1="24" y1="77" x2="117" y2="77"
                                                    gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#B224EF" stop-opacity="0.41"/>
                                        <stop offset="1" stop-color="#7579FF"/>
                                    </linearGradient>
                                    <linearGradient id="paint1_linear_30_443" x1="24" y1="77" x2="117" y2="77"
                                                    gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#B224EF"/>
                                        <stop offset="1" stop-color="#7579FF"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="profile-dignity__content">
                            <p class="profile-dignity__text">Ваше место среди донатеров: {{$ratingPosition}}</p>
                            <a class="profile-dignity__link row ai-center dignity-popup-link" href="#dignity">Посмотреть
                                все титулы<span>&#8250;</span></a>
                        </div>
                    </div>
                    <div class="profile-donate">Процент от донатов<span>{{$donatersPercent}}%</span></div>
                </div>
            </div>
            @if($user->is_admin())
                <div class="profile-statistics bg-dark" style="margin-bottom: 20px">
                    <?php

                        $serviceBalances = app(\App\Services\ServiceBalanceStatisticsService::class)->latest();

                        ?>
                    <style>
                        .balance_data {
                            width: 100%;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            padding: 15px 0;
                            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
                        }
                        .balance_data:last-child {
                            border-bottom: 0;
                        }
                        .balance_data span, .balance_data ul {
                            text-align: left;
                            min-width: 300px;
                        }
                    </style>

                    <h2 class="profile-statistics__title">Баланс проекта</h2>

                    <div class="profile-statistics__row mb-8">
                        <div class="profile-statistics__content">
                            <div class="balance_data">Баланс DEELS: <span class="d-flex align-content-center">{{ $project_wallet_balance ?? 0 }} <img src="/dist/img/deels_cur.svg" class="small_coin"></span></div>
                        </div>
                    </div>

                    <h2 class="profile-statistics__title">Статус сервисов</h2>
                    <div class="profile-statistics__row">
                        <div class="profile-statistics__content">
                            <div class="balance_data">Баланс Ucaller: <span>{{ $serviceBalances['ucaller_balance'] ?? 0 }} ₽</span></div>
                            <div class="balance_data">Баланс SMSC: <span>{{ $serviceBalances['sms_balance'] ?? 0 }} ₽</span></div>
                            <div class="balance_data">Баланс proxy6.net: <span>{{ $serviceBalances['proxy_balance'] ?? 0 }} ₽</span></div>
                            @if(!empty($serviceBalances['proxies']))
                            <div class="balance_data">Список прокси:
                                <ul>
                                    @foreach($serviceBalances['proxies'] as $key => $proxy)
                                    <li>{{$proxy['ip']}} до {{\Carbon\Carbon::parse($proxy['date_end'])->format('d.m.Y H:s')}}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            <small class="d-block mt-6">Данные обновляются раз в 30 мин</small>
                        </div>
                    </div>
                </div>
            @endif


            <div class="profile-reflink">
                <button class="btn_fill profile-reflink__btn" onclick="$(this).next().click()">Моя ссылка</button>
                <div class="profile-reflink__text copy-text" data-text="Ссылка скопирована">
                    {{url('/')}}/register?ref={{$user->referral_code}}</div>
            </div>
            <div class="profile-statistics bg-dark" style="margin-bottom: 40px">
                <h2 class="profile-statistics__title">Статистика</h2>
                <div class="profile-statistics__row">
                    <div class="profile-statistics__chart">
                        <!--значение указывать в атрибут data-progress-count-->
                        <div class="profile-progress"
                             data-progress-count="{{$donatersLevelFill>0 ? $donatersLevelFill : 1}}">
                            <svg>
                                <circle class="profile-progress__bg" cx="115" cy="115" r="95"></circle>
                                <!-- значение указывать в стиль инлайново (в данном случае 76)-->
                                <circle class="profile-progress__bar" cx="115" cy="115" r="95"
                                        style="stroke-dashoffset: calc(596 - (596 * {{$donatersLevelFill>0 ? $donatersLevelFill : 1}}) / 100)"></circle>
                                <defs>
                                    <linearGradient id="chart1Grad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop stop-color="#B224EF"></stop>
                                        <stop offset="1" stop-color="#7579FF"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <div class="profile-statistics__content">
                        <p>Пришло посетителей: {{count($myDonaters)}}</p>
{{--                        <p>Оплаченных переходов: {{count($myDonatersPayments)}}</p>--}}
                        @if($donatersPercent > 0)
                            <p>Текущий доход: {{$donatersPercent}}% </p>
                        @endif
                        <p>Заработано на посетителях: {{($total_profit_achieve)}} ₽ </p>
                        @if(count($myDonatersPayments))
                        <div class="profile-table-link text-shadow">Показать детальнее</div>
                        @endif
                    </div>
                </div>
                <div class="profile-table">
                    <div class="profile-table__head">
                        <div class="profile-table__row">
                            <div class="profile-table__col">
                                <p>Пользователь</p>
                            </div>
                            <div class="profile-table__col">
                                <p>Сумма доната </p>
                            </div>
                            <div class="profile-table__col">
                                <p>Заработанная сумма</p>
                            </div>
                            <div class="profile-table__col">
                                <p>Статус</p>
                            </div>
                        </div>
                    </div>
                    <div class="profile-table__body">
                       @if(count($myDonatersPayments))
                        @foreach($myDonatersPayments as $myDonatersPayment)
                            <div class="profile-table__row">
                                <div class="profile-table__col">
                                    <div class="d-flex ai-center">
                                        <div class="avatar avatar--xs mr-1"><img
                                                    src="{{$myDonatersPayment->avatar()}}" alt=""/>
                                        </div>
                                        <p class="text-shadow">{{$myDonatersPayment->username}}</p>
                                    </div>
                                </div>
                                <div class="profile-table__col">
                                    <div class="d-flex ai-center"><span>Сумма доната:</span>
                                        <p class="text-shadow">{{$myDonatersPayment->paymentsAmount ?? 0}}₽</p>
                                    </div>
                                </div>
                                <div class="profile-table__col">
                                    <div class="d-flex ai-center"><span>Заработанная сумма:</span>
                                        <p class="text-shadow">{{$myDonatersPayment->totalProfit ?? 0}} ₽</p>
                                    </div>
                                </div>
                                <div class="profile-table__col">
                                    <div class="d-flex ai-center"><span>Статус:</span>
                                        @if($myDonatersPayment->paymentsAmount>0)
                                            <div class="icon-status icon-status--success"></div>
                                        @else
                                            <div class="icon-status icon-status--error"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @else
                            <div class="profile-table__row">
                                <div class="profile-table__col">
                                    Данных еще нет
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="popup dignity mfp-hide" id="dignity">
            <div class="popup-head">
                <h2 class="popup-head__title">Ваш титул: {{empty($myDonaters) || (!empty($myDonaters) && $myDonaters->count()<5) ?  'Без титула':'Бронза'}}</h2>
                <p class="popup-head__text">уровень аккаунта: {{$currentPosition}}</p>
            </div>
            <div class="popup-body">
                <div class="dignity__progress">
                    <div class="dignity-progress">
                        <div class="dignity-progress__bar" style="width: {{$offset}}%"></div>
                    </div>
                    <div class="dignity-counts">
                        @if(!empty($myDonaters))
                        <div class="dignity-count @if($myDonaters->count()>=5 && $myDonaters->count()<10) active @endif">
                            5
                        </div>
                        <div class="dignity-count @if($myDonaters->count()>=10 && $myDonaters->count()<50) active @endif">
                            10
                        </div>
                        <div class="dignity-count @if($myDonaters->count()>=50 && $myDonaters->count()<100) active @endif">
                            50
                        </div>
                        <div class="dignity-count @if($myDonaters->count()>=100 && $myDonaters->count()<200) active @endif">
                            100
                        </div>

                        <div class="dignity-count @if($myDonaters->count()>=200) active @endif">200</div>
                        @else
                            <div class="dignity-count">
                                0
                            </div>
                        @endif
                    </div>
                </div>
                <div class="dignity-icons">
                    <img src="/dist/images/admin_icons/dignity-1.svg" alt=""><img
                            src="/dist/images/admin_icons/dignity-2.svg" alt=""><img
                            src="/dist/images/admin_icons/dignity-3.svg" alt=""><img
                            src="/dist/images/admin_icons/dignity-4.svg" alt=""><img
                            src="/dist/images/admin_icons/dignity-5.svg" alt="">
                </div>
            </div>
        </div>

        <div class="popup dignity mfp-hide" id="profile_picture">
            <div class="popup-head">
                <h2 class="popup-head__title">Выбрать из списка</h2>
            </div>
            <form enctype="multipart/form-data" method="post" class="popup-body" action="{{route("change_avatar")}}">
                @csrf
                <div class="dignity__progress d-flex flex-wrap">
                    {{--                    currently 6 avatars--}}

                    @for($i = 1 ;$i <= 6;++$i)
                        <div class="default_avatar_item form-inputs d-flex flex-column align-items-center" style="width: 33%; @if($i<=3) margin: 0 auto 30px @else margin: auto @endif">
                            <label for="avatar_{{$i}}">
                                <img src="/default_avatars/avatar_{{$i}}.png" alt="">
                            </label>
                            <input type="radio" name="default_avatar" @if($i === 1)checked="true"
                                   @endif id="avatar_{{$i}}" value="/default_avatars/avatar_{{$i}}.png">
                        </div>
                    @endfor
                </div>
                <div class="popup-head">
                    <h2 class="popup-head__title">Или</h2>
                </div>

                <label class="form-input row ai-center">
                    <input type="file" class="avatar_input" name="avatar"><i class="profile-info-form__icon mr-1">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.32737 2.50331L12.3787 5.56956L4.65493 13.3312L1.60532 10.2649L9.32737 2.50331ZM14.6941 1.7638L13.3333 0.396352C12.8074 -0.132117 11.9535 -0.132117 11.4258 0.396352L10.1223 1.70623L13.1736 4.77251L14.6941 3.24461C15.102 2.8347 15.102 2.17368 14.6941 1.7638ZM0.00849116 14.5748C-0.0470393 14.8259 0.178599 15.051 0.428543 14.9899L3.82875 14.1614L0.779139 11.0952L0.00849116 14.5748Z"
                                  fill="white"/>
                        </svg>
                    </i>
                    <h2 class="profile-info-form__text">Загрузить из устройства</h2>

                    <img id="blah" src="" style="
    max-width: 100px;
    display: inline-block;
    margin-left: 16px;
">
                </label>

                <br>

                <div class="w-100 d-flex justify-content-center">
                    <button type="submit" class="btn_fill profile-reflink__btn save_avatar">Сохранить</button>
                </div>
            </form>

        </div>
    </main>
@endsection

@section('page-js')
    <script>
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#blah').attr('src', e.target.result);
				}

				reader.readAsDataURL(input.files[0]);
			}
		}

        $(document).ready(function() {
            // Check for changes on the radio buttons
            $('.default_avatar_item').click(function() {
                $('.form-inputs label').removeClass('checked');
                // Add 'checked' class to the parent label of the checked radio button
                $(this).find('label').addClass('checked');
                $('.save_avatar').attr('readonly', false);
                $('.save_avatar').prop('readonly', false);
                $('.save_avatar').removeAttr('readonly');
            });
        });

		$(".avatar_input").change(function(){
			readURL(this);
            $('.form-inputs label').removeClass('checked');
            $('.save_avatar').attr('readonly', false);
            $('.save_avatar').prop('readonly', false);
            $('.save_avatar').removeAttr('readonly');

		});
    </script>
@endsection

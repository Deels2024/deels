@extends('layouts.new.app')

@section('title') @if(! empty($title)) {!!$title!!} @endif  @parent @endsection

@section('content')

    <section class="page-top">
        <div class="wrapper">
            <div class="breadcrumbs">
                <ul>
                    <li>
                        <a href="/">Главная</a>
                    </li>
                    <li>
                        <a href="/dashboard">Личный кабинет</a>
                    </li>
                    <li>Вывод средств</li>
                </ul>
            </div>
            <div class="page-top__title title title_big">Личный кабинет</div>
        </div>
    </section>

    <section class="cabinet cabinet_right">
        <div class="wrapper cabinet__wrap flex">
            <div class="cabinet__left mobile-window">
                <div class="mobile-window__closed img-contain">
                    <img src="/images/icons/close.svg" alt="">
                </div>
                <div class="cabinet-sidebar">
                    @include('admin.menu')
                </div>
            </div>
            <div class="cabinet__right">
                <div class="cabinet__right-title">Вывод средств</div>
                <div class="cabinet__filter filter-mobile btn">
                    <img src="/images/icons/filter.svg" alt="">
                    <span>Открыть меню</span>
                </div>
                <div class="cabinet__box">
                    @if (session()->has('success'))
                        <div style="padding: 20px;background-color: rgba(124, 199, 23,0.7);margin-bottom: 20px;color: #fff;">
                            <b>Успех!</b> Ваш запрос на вывод средств отправлен, ожидайте обратной связи!
                        </div>
                    @endif
{{--                    <div class="cabinet__top flex">--}}
{{--                        <div class="cabinet__caption">Вы получите 80% от суммы финансирования</div>--}}
{{--                    </div>--}}
                    <div class="cabinet-profile">
                        <form action="" class="cabinet-profile__form" method="post">
                            @csrf
                            <div class="cabinet-profile__block flex">
                                <div class="cabinet-profile__caption">Копилка</div>
                                <div class="cabinet-profile__block-label">
                                    <div class="select">
                                        <div class="select-title flex">
                                            <div class="select-title__value text">Выберите копилку</div>
                                            <div class="select-title__arrow img-contain">
                                                <img src="/images/icons/sidebar-arrow.svg" alt="">
                                            </div>
                                        </div>
                                        <div class="select-options">
                                            <div class="select-options__value text">Выберите копилку</div>
                                            @foreach($campaigns as $campaign)
                                                <div class="select-options__value text" data-value="{{$campaign->id}}">{{$campaign->name}}</div>
                                            @endforeach
                                            <input type="hidden" name="withdrawal_campaign_id" value="Выберите кампанию">
                                        </div>
                                    </div>
                                </div>
                                <div class="cabinet-profile__block-last">
                                    <label class="checkbox">
                                        <input type="checkbox" value="checkbox" name="all_campaigns" checked>
                                        <span>Вывести со всех копилок</span>
                                    </label>
                                </div>
                            </div>
                            <div class="cabinet-profile__block flex">
                                <?php
                                $totalAmount = 0;
                                foreach ($campaigns as $campaign) {
                                    $totalAmount += $campaign->amount_raised()->amount_raised;
                                }?>
                                <div style="display: none" class="cabinet-profile__caption">Сумма</div>
                                <div style="display: none" class="cabinet-profile__block-label">
                                    <input type="number" value="{{$totalAmount}}" name="price" class="cabinet-profile__field field" max="{{$totalAmount}}" placeholder="Макс. {{$totalAmount}}₽">
                                </div>
                                <div class="cabinet-profile__block-last withdrawBtn" style="display: none">
                                    <button class="cabinet-profile__btn btn">Вывести средства</button>
                                </div>
                            </div>
                            <div class="cabinet-profile__block flex">
                                <label class="campaign-form__label codeBlock" style="width: calc(80% - 10px);">
                                    <div class="campaign-form__caption">Код подтверждения из письма</div>
                                    <input type="text" name="code" class="campaign-form__field field" style="width: 37%" required>
                                    <button type="button" class="btn sendCodeBtn">
                                        Получить код
                                    </button>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('page-js')
    <script>
		$('.sendCodeBtn').click(function () {
			alert('Код был отправлен вам на почту');
			$.post('/user/sendEmailCode', {email: '{{Auth::user()->email}}'}, function () {
				$('.withdrawBtn').show();
            });
			$(this).attr('disabled', 'disabled')
		});
    </script>
@endsection

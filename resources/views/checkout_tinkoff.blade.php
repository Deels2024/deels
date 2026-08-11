@extends('layouts.neon.app')
@section('title')
    @if( ! empty($title))
        {!! $title !!} |
    @endif @parent
@endsection

@section('content')

    <div class="background__dark"></div>

    <div class="sign">
        <div class="container">
            <h2>Вклад в копилку
                <span>Вклад в копилку</span>
            </h2>
            <div class="deposit__pre account__pre account__pre-big">Гостевой заказ или
                <a href="{{route('login')}}" class="">Войти</a>
            </div>
            <script src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff_v2.js"></script>
            <div class="deposit__pre account__pre account__pre-big">
                <form name="TinkoffPayForm" action="" class="form deposit__form form-horizontal TinkoffPayForm" method="post" enctype="multipart/form-data"> @csrf
                    <input class="tinkoffPayRow" type="hidden" name="terminalkey" value="1619081031059">
                    <input class="tinkoffPayRow" type="hidden" name="SuccessURL" value="{{url('/campaign/'.$campaign->slug)}}">
                    <input class="tinkoffPayRow" type="hidden" name="FailURL" value="{{url('/campaign/'.$campaign->slug)}}">
                    <input class="tinkoffPayRow" type="hidden" name="successURL" value="{{url('/campaign/'.$campaign->slug)}}">
                    <input class="tinkoffPayRow" type="hidden" name="frame" value="true">
                    <input class="tinkoffPayRow" type="hidden" name="language" value="ru">
                    <input class="tinkoffPayRow" type="hidden" name="reccurentPayment" value="{{request()->has('auto') ? 'true' : 'false'}}">
                    <input class="tinkoffPayRow" type="hidden" name="customerKey" value="{{Auth::id() ?? 'anon_'.time()}}">
                    <input class="tinkoffPayRow receiptTinkoff" type="hidden" name="receipt" value=''>

                    <div class="deposit__name">
                        <label for="name">Полное имя*</label>
                        <input type="text" name="name" id="name" value="@if(Auth::check()){!!auth()->user()->fullname!!}@else{!! old('full_name') !!}@endif" placeholder="Артем">
                        <div class="deposit__name-hide">Заполните это поле</div>
                    </div>
                    <div class="deposit__name">
                        <label for="email">Email</label>
                        <input type="text" value="@if(Auth::check()){!!auth()->user()->email!!}@else{!! old('email') !!}@endif" name="email" id="email" placeholder="1234@gmail.com">
                        <div class="deposit__name-hide">Заполните это поле</div>
                    </div>
                    <div class="account__pre account__pre-big">Общая сумма</div>
                    @php
                        if(session('cart.cart_type') == 'reward'){
                        $donation_amount = $reward->amount;
                        }else{
                        $donation_amount = session('cart.amount');
                        }
                        $donation_amount = (int)str_replace('.', '', $donation_amount);

                        $orderID = time().'_'.(auth()->id() ?? 'anon').'_'.$campaign->id;

                           $api = new App\TinkoffService(
                                '1619081031059',
                                'i0hikbqorpis86rw'
                            );

                            $params = [
                                'TerminalKey' => '1619081031059',
                                'OrderId'     => $orderID,
                                'Amount'      => $donation_amount * 100,
                                'Taxation'    => 'usn_income',
                                'SuccessURL' => url('/campaign/'.$campaign->slug),
                                'FailURL' => url('/campaign/'.$campaign->slug),
                                'Receipt'     => [
                                    'Taxation' => 'usn_income',
                                    'Email'    => auth()->user()?->email ?? 'anon@email.ru',
                                    'Items'    => [
                                        [
                                            'Name'          => "Донат в копилку " . htmlspecialchars(mb_substr($campaign->title, 0, 75)),
                                            'Price'         => $donation_amount * 100,
                                            'Quantity'      => 1,
                                            'Amount'        => $donation_amount * 100,
                                            'PaymentMethod' => 'full_payment',
                                            'PaymentObject' => 'commodity',
                                            'Tax'           => 'none',
                                        ],
                                    ],
                                ],
                                'DATA'        => [
                                    'Email'           => auth()->user()->email ?? 'anon@email.ru',
                                    'Connection_type' => 'example',
                                ],
                            ];

                            $init = json_decode($api->init($params), true);


                            $qr = json_decode($api->getQr([
                                'TerminalKey' => '1619081031059',
                                'PaymentId'   => $init['PaymentId'],
                                'DataType'    => 'PAYLOAD',
                            ]), true);
                    @endphp
                    <div class="deposit__title">{!!$campaign->title!!} - {!!get_amount($donation_amount)!!}</div>
                    <div class="deposit__title">Итог - {!!get_amount($donation_amount)!!}</div>
                    <input type="button" class="deposit__btn deposit__btn_pay btn btn_fill" value="Оплатить">
                    <div id="tinkoffWidgetContainer1" style="margin-top: 20px"></div>
                    <?php /*
                    <input type="button" style="display: block" onclick="window.location='{{$qr['Data']}}'" class="deposit__btn btn btn_fill" value="Оплатить по СБП">
                    */?>
                    <div class="deposit__text">
                        Вы также признаете и соглашаетесь с <a href="/docs/file1.docx" download>Условиями использования</a> и
                        <a href="/docs/privacy_policy.docx" download>Политикой конфиденциальности</a>.
                    </div>
                    <input class="tinkoffPayRow" type="hidden" placeholder="Сумма заказа" value="{{$donation_amount}}" name="amount" required>
                    <input class="tinkoffPayRow orderRow" type="hidden" placeholder="Номер заказа" name="order" value="{{time().'_'.(Auth::id() ?? 'anon').'_'.$campaign->id}}">
                    <input class="tinkoffPayRow" type="hidden" placeholder="Описание заказа" value="{{'Донат в копилку ' . $campaign->title}}" name="description">
                    <input class="tinkoffPayRow" type="hidden" name="receipt" value="">
                    <input class="tinkoffPayRow" type="hidden" name="phone" value="">
                </form>

                <script>
                    const terminalkey = document.forms.TinkoffPayForm.terminalkey;
                    const form = document.forms.TinkoffPayForm;

                    // Данные для чека
                    Object.defineProperty(form.receipt, 'value', {
                        get: function () {
                            return JSON.stringify({
                                Email: form.email.value,
                                Phone: form.phone.value,
                                EmailCompany: 'info@deels.ru',
                                Taxation: 'usn_income',
                                Items: [
                                    {
                                        Name: form.description.value || 'Оплата',
                                        Price: form.amount.value + '00',
                                        Quantity: 1.0,
                                        Amount: form.amount.value + '00',
                                        PaymentMethod: 'full_prepayment',
                                        PaymentObject: 'commodity',
                                        Tax: 'none',
                                    },
                                ],
                            });
                        },
                    });

                    const widgetParameters = {
                        terminalKey: terminalkey.value,
                        paymentItems: [
                            {
                                container: document.getElementById("tinkoffWidgetContainer1"),
                                paymentInfo: function () {
                                    return {
                                        paymentData: document.forms.TinkoffPayForm,
                                    };
                                },
                            },
                        ],
                        paymentSystems: { TinkoffFps: {} },
                    };

                    window.addEventListener("load", function () {
                        initPayments(widgetParameters);
                    });
                </script>
            </div>
        </div>
    </div>

@endsection

@section('page-js')

    <script>
		$(function () {
            setCookie('payed_campaign', '{{$campaign->slug}}')
			$(document).on('click', '.donate-amount-placeholder ul li', function (e) {
				$(this).closest('form').find($('[name="amount"]')).val($(this).data('value'));
			});
            $(document).on('click', '.deposit__btn_pay', function (e) {
                $('.TinkoffPayForm').submit();
			});

			$('.TinkoffPayForm').submit(function (e) {
				e.preventDefault();

				let orderVal = $('.orderRow').val()
				$('.orderRow').val(orderVal + '_' + $('#email').val())

				let receiptData = {
					"Email": $('#email').val(),
					"Taxation": "usn_income",
					"Items": [{
						"Name": "{!! 'Донат в копилку ' . htmlspecialchars(mb_substr($campaign->title, 0, 75)) !!}",
						"Price": {{$donation_amount*100}},
						"Quantity": 1.00,
						"Amount": {{$donation_amount*100}},
						"PaymentMethod": "full_payment",
						"PaymentObject": "commodity",
						"Tax": "none"
					}]
				}
				$('.receiptTinkoff').val(JSON.stringify(receiptData));
				pay(this);
				return false;
			});
			$(document).on('click', '.t-close-frame-desktop', function () {
				window.location = '{{url('/campaign/'.$campaign->slug)}}'
			})
		});
    </script>

@endsection

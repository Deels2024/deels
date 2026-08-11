@extends('layouts.neon.app')
@section('title')
    Активация аккаунта
@endsection
@push('meta-data')
    <meta name="robots" content="noindex, nofollow" />
@endpush
@section('content')
    <style>
        input:focus::placeholder {
            color: transparent!important;
        }
    </style>
    <div class="sign">
        <div class="container">
            <h2>Подтвердите ваш номер телефона
                <span>Подтвердите ваш номер телефона</span>
            </h2>

            <form class="form sign__form validated_form" role="form" method="POST" action="{{ route('activation_verify') }}" autocomplete="off" role="presentation">

                @csrf
                <input type="hidden" name="source" value="sms_web">

                @if (session('error'))
                    <div class="page-content" style="width: 100%; padding: 0!important; margin: 0!important;">
                        <p class="" style="color: #d64c4c">
                            {!! session('error') !!}
                        </p>
                    </div>
                @endif

                @php
                    $user_message = '';
                    $phone_activation = \App\Models\UserActivation::where('user_id', Auth::user()->id)->where('type', 'phone')->first();
                    $phone_attempts_exhausted = $phone_activation
                        && ($phone_activation->verify_phone_data['attempts_date'] ?? null) === now()->toDateString()
                        && (int) ($phone_activation->verify_phone_data['attempts'] ?? 0) >= 3;
                    if($phone_activation && isset($phone_activation->verify_phone_data['requested'])) {
                        $method = 'совершен звонок в';
                        $method_verify = 'введите последние 4 цифры номера телефона входящего звонка';
                        if(isset($phone_activation->verify_phone_data['type'])) {
                            if($phone_activation->verify_phone_data['type'] == 'sms') {
                                $method = 'отправлено SMS-сообщение';
                                $method_verify = 'введите код из сообщения';
                            }
                        }
                        $user_message = '<p>На номер <strong class="text-black-50 font-weight-bolder">' . $phone_activation->phone . '</strong> '.$method.' в <strong class="text-black-50 font-weight-bolder">' . \Carbon\Carbon::parse($phone_activation->verify_phone_data['requested'])->timezone('Europe/Moscow')->subMinute()->format('H:i:s') . '</strong>';
                        $user_message .= '<br><br>Пожалуйста, <strong class="text-black-50 font-weight-bolder">'.$method_verify.'</strong> в поле ниже</p>';
                    }

                @endphp

                @if($phone_activation)
                    @if(!$phone_activation->is_verified)
                        <div class="page-content phone_message" style="width: 100%; padding: 0!important; margin: 40px 0 0 0!important;">
                            {!! $user_message !!}
                        </div>
                        <label>
                            <i class="error_message"></i>
                            <input type="text" placeholder="Введите код подтверждения" name="phone_code" class="required">
                        </label>
                        <div class="phone_attempts_exhausted" @if(!$phone_attempts_exhausted) style="display:none" @endif>Закончились попытки, попробуйте снова завтра или обратитесь в поддержку</div>
                        <div class="phone_resend_actions" @if($phone_attempts_exhausted) style="display:none" @endif>
                            <a href="#" class="form__btn btn btn_fill btn-small btn-grey resend sms_resend active" data-text="Получить SMS" data-type="sms" data-next="{{\Carbon\Carbon::parse($phone_activation->verify_phone_data['requested'] ?? \Carbon\Carbon::now())->timezone('Europe/Moscow')->format('Y/m/d H:i:s')}}">Получить SMS</a>
                            <a href="#" class="form__btn btn btn_fill btn-small btn-grey resend phone_resend active" data-text="Запросить звонок" data-type="phone" data-next="{{\Carbon\Carbon::parse($phone_activation->verify_phone_data['requested'] ?? \Carbon\Carbon::now())->timezone('Europe/Moscow')->format('Y/m/d H:i:s')}}">Запросить звонок</a>
                        </div>
                    @endif
                    <br>
                    <div class="form__btns">
                        <button type="submit" class="form__btn btn btn_fill">Завершить регистрацию</button>

                        <a href="{{route('logout')}}">Выйти из аккаунта</a>


                    </div>
                @else
                    <div class="page-content phone_message" style="width: 100%; padding: 0!important; margin: 40px 0 0 0!important;">
                        Укажите ваш номер телефона
                    </div>
                    <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                    <label>
                        <i class="error_message"></i>
                        <input type="text" placeholder="Укажите ваш номер телефона" name="phone" class="required">
                    </label>

                    <div class="form__btns">
                        <button type="submit" class="form__btn btn btn_fill">Продолжить</button>

                        <a href="{{route('logout')}}">Выйти из аккаунта</a>


                    </div>
                @endif



            </form>
        </div>
    </div>
@endsection

@if(get_option('enable_recaptcha_registration') == 1)
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif


@section('page-js')
    <script src="{{ext_asset('/dist/js/validations.js')}}"></script>
    <script src="/dist/js/jquery.countdown.min.js"></script>
    <script>
        function countDown() {
            $('.resend').each(function() {
                var element = $(this);
                var type = $(this).attr('data-type');
                setTimeout(function () {
                    var time = element.attr('data-next');
                    element
                        .countdown(time, function(event) {
                            if(event.strftime('%M:%S') == '00:00') {
                                element.addClass('active');
                                element.text(element.attr('data-text'));
                            } else {
                                element.removeClass('active');
                                if(type == 'phone') {
                                    element.text(
                                        event.strftime('Запрос звонка через: %M:%S')
                                    );
                                } else if(type == 'sms') {
                                    element.text(
                                        event.strftime('Запрос sms через: %M:%S')
                                    );
                                } else {
                                    element.text(
                                        event.strftime('Повторный запрос через: %M:%S')
                                    );
                                }

                            }
                        });
                }, 300);

            });
        }

        countDown();
        $('body').on('click', ".resend", function(e) {
            e.preventDefault();
            e.stopPropagation();
            if($(this).hasClass('active')) {
                var type = $(this).attr('data-type');
                $.ajax({
                    url: '{{route('activation_resend')}}',
                    type: 'POST',
                    data: {
                        type: type,
                        user_id: '{{Auth::user()->id}}',
                        source: $('[name="source"]').val()
                    },
                    success: function(result) {
                        console.log(result);
                        if(result.success) {
                            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Запрошена повторная активация!</div>')
                            if(result.action == 'phone') {
                                $('.phone_message').html(result.message);
                                $('.phone_resend').attr('data-next', result.time);
                            }
                            if(result.action == 'sms') {
                                $('.phone_message').html(result.message);
                                $('.sms_resend').attr('data-next', result.time);
                            }
                            if(result.attempts_left === 0) {
                                $('.phone_resend_actions').hide();
                                $('.phone_attempts_exhausted').show();
                            }
                        } else {
                            $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+result.message+'</div>')
                            if(result.limit_reached) {
                                $('.phone_resend_actions').hide();
                                $('.phone_attempts_exhausted').show();
                            }
                        }
                        countDown();
                    },
                    error: function(result) {
                        new Noty({
                            type: "danger",
                            text: "Произошла ошибка",
                            timeout: 5000,
                            killer: true,
                        }).show();
                        countDown();
                    }
                });
            }
        });
    </script>
@endsection
@section('page-css')
    <style>
        .campaign-form__field {
            margin: 7px 0 !important;
        }

        .campaign-form__label {
            margin-top: 30px;
        }
    </style>
@endsection

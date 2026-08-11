@extends('layouts.neon.app_light')
@section('title')
   Регистрация
@endsection
@push('meta-data')
    <meta name="robots" content="noindex, nofollow" />
@endpush
@section('content')
    <a href="/challenges/show/117" class="popup-try d-flex" target="_blank">
        <img src="/dist/images/popup-try_30.png" alt="" class="popup-try__img">
        <p class="popup-try__text txt-neon-white">Попробуй челлендж!<br>Приз 30 000 ₽</p>
    </a>
    <header>
        <div class="header">
            <div class="container">
                <a class="header__logo" href="{{url('/')}}"> <img src="/dist/images/icons/deels.svg" alt="DEELS"></a>
                <div class="header__list desk">
                    <ul>
                        <li>
                            <a href="/about-us">О нас</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <style>
        body {
            background-image: url(/dist/images/background.png);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .login{
            margin-top: 60px;
        }
        .header{
            justify-content: space-between;
        }
        .header__list{
            position: initial;
        }
        .header__list a{
            text-transform: uppercase;
            font-size: 18px;
            font-weight: 300;
        }
        .login__container{
            gap: 20px;
            margin-bottom: 65px;
        }
        .login__left{
            max-height: 660px;
            width: 100%;
            max-width: 544px;
        }

        .header__logo img{
            width: 127px;
            height: 35px;
        }
        .login__right{
            margin-top: 34px;
            min-width: 400px;
            max-width: 540px;
        }
        .login__title{
            font-size: 40px;
            font-weight: 700;
        }
        .txt-neon-white{
            filter: drop-shadow(3px 1px 17px #FFFFFF);
        }
        .login__text{
            color: white;
        }
        .auth-input{
            font-size: 16px!important;
            padding: 22px 20px!important;
            border-radius: 8px!important;
            width: 100%!important;
            max-width: 540px!important;
            background-color: transparent!important;
            border: 1px solid #D9D9D9!important;
            color: white!important;
            font-family: "Gilroy", sans-serif!important;
        }
        .auth-input::placeholder {
            color: white!important;
        }
        .mb-40{
            margin-bottom: 40px;
        }
        .login__btn-big , .login__btn-small{
            margin: 0;
            font-size: 18px;
            padding: 14px 0;
            width: 60%;
        }
        .login__container{
            background: transparent;
        }
        .login__btn-small{
            width: 34%;
        }
        .link-forget{
            text-decoration: underline;
        }
        .login__btns{
            justify-content: space-between;
        }
        .login__form{
            margin-bottom: 32px;
        }
        .login__form .form__agree label {
            display: block;
            line-height: 1.25;
            max-width: calc(100% - 49px);
        }
        .login__form .form__agree label a {
            display: inline !important;
            padding-left: 0 !important;
            white-space: normal;
        }
        .login__alter{
            flex-direction: column;
            gap: 15px;
        }
        .login__alter-btns{
            align-items: center;
            gap: 15px;
        }
        .login__alter-btn{
            border: 1px solid #00F0FF;
            border-radius: 8px;
            width: 54px;
            height: 54px;
            align-items: center;
            justify-content: center;
        }
        .login__alter-ico{
            width: 90%;
            height: 90%;
        }
        .advantages__list{
            text-align: center;
            margin-bottom: 70px;
        }
        .advantages__item{
            font-size: 40px;
            font-weight: 600;
        }
        .advantages__item:last-child{
            font-size: 32px;
            font-weight: 500;
        }
        .popup-try{
            position: absolute;
            right: 0;
            bottom: 170px;
            background-color: #4086b9;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            padding: 21px 21px 0 14px;
            z-index: 999;

        }
        .popup-try__text{
            font-size: 30px;
            max-width: 193px;
        }
        main{
            margin: auto;
        }
        @media(max-height:1040px){
            .login{
                margin-top: 20px;
            }
            .login__container{
                margin-bottom: 0;
            }
        }
        @media(max-height:970px){
            .login__form{
                margin-bottom: 16px;
            }
            .login__left{
                max-height: 600px;
            }
            .popup-try{
                bottom: 200px;
            }

        }
        @media(max-height:950px){
            .advantages__list{
                margin-bottom: 0;
            }
            .popup-try {
                bottom: 80px;
                padding: 16px 24px 0 12px;
                justify-content: center;
            }
            .popup-try__text {
                font-size: 24px;
                max-width: 150px;
            }
            .advantages__item{
                font-size: 32px;
            }
            .advantages__item:last-child {
                font-size: 24px;

            }
            .auth-input{
                padding: 16px 20px;
            }
        }
        @media(max-width: 767px){
            .login__container{
                flex-direction: column-reverse;
                align-items: center;
            }
            .login__left img{
                max-width: 40%;
                margin: 0 auto;
                display: block;
            }
            .advantages{
                display: none;
            }
            .popup-try{
                bottom: 0;
                width: 100%;
                border-radius: 0;
                align-items:center;
                padding: 17px 40px 0 23px;

            }
            .popup-try__img{
                width: 110px;
            }
            .popup-try__text{
                max-width: 100%;
                font-size: 20px;
                text-align: center;
            }
        }
        @media(max-width: 575px){
            .header__list{
                display: block !important;
                margin-right: 0;
                font-size: 16px;
            }
            .header{
                padding: 0;
                height: 60px;
            }
            .header__logo img{
                width: 74px;
                height: 20px;
            }
            .login__container{
                gap: 0;
            }
            .login{
                margin-top: 50px;
            }
            .login__right{
                min-width: initial;
                margin-top: 0;
            }
            .login__left img {
                margin-bottom: 45px;
            }
            .login__title{
                text-align: center;
                font-size: 32px;
                margin-bottom: 6px;
            }
            .login__text{
                text-align: center;
                font-size: 16px;
                margin-bottom: 12px;
            }
            .mobile-border-bottom{
                border-bottom: 1px solid #00F0FF;
                padding: 5px 0;
                display: inline-block;
                width: 280px;
            }
            .mobile-border-bottom:last-child{
                margin-bottom: 12px;
            }
            .mobile-border-bottom .text-accent{
                width: 100%;
                white-space: nowrap;
            }
            .mb-40{
                margin-bottom: 20px;
            }
            .login__btns{
                flex-direction: column;
                gap: 12px;
            }
            .login .start__btn , .login .btn_fill{
                width: 100%;
            }
            .link-forget{
                display: block;
                text-align: center;
                font-size: 12px;
            }
            .login__form{
                margin-bottom: 12px;
            }
            .login__text{
                margin: 0;
            }
            .login__alter-btns{
                justify-content: center;
                width: 44px;
                height: 44px;
            }
            .auth-input {
                padding: 12px 20px;
            }
            .advantages{
                display: none;
            }

        }
        @media(min-width:575px){
            .login__text .text-accent{
                color: white;
            }
        }

        input:focus::placeholder {
            color: transparent!important;
        }
        .check {
            display: none;
            position: absolute;
            right: 20px;
            top: 50%;
            margin-top: -12px;
            transform: rotate(45deg);
            height: 16px;
            width: 8px;
            border-bottom: 3px solid #78b13f;
            border-right: 3px solid #78b13f;
        }
        .valid {
            position: relative;
        }
        .valid .check {

            display: block;
        }
        .form-field.error .check {
            display: none !important;
        }
        .form-field {
            max-width: 450px;
        }
        .form-field.error {
            margin-bottom: 20px;
        }
        .registration-server-errors {
            display: block !important;
            width: 100%;
            margin-bottom: 20px;
            padding: 12px 16px;
            border: 1px solid #dc3545;
            border-radius: 8px;
            background: rgba(220, 53, 69, 0.12);
            color: #ff6b78 !important;
            font-size: 14px;
            line-height: 1.4;
        }
        .registration-server-errors div + div {
            margin-top: 6px;
        }
        .email-code-field.error, .email-code-field input {
            margin-bottom: 0!important;
        }


    </style>

    <main>
        <section class="login">
            <div class="container login__container d-flex">
                <div class="login__left">
                    <img loading="lazy" src="dist/images/pig-big.png" alt="копилка" class="login__img">
                </div>
                <div class="login__right">
                    <h2 class="login__title txt-neon-white mb-5">Регистрация</h2>
                    <form  class="sign__form login__form login__form validated_form" role="form" method="POST" action="{{ route('register') }}" autocomplete="off" role="presentation">
                        @if(session('error'))
                            <div class="alert alert-danger" style="margin-bottom: 20px; display:block; width: 100%; color: #ff0000; background: transparent!important;">
                                {!! session('error') !!}
                            </div>
                        @endif
                        @if(isset($errors) && $errors->any())
                            <div class="registration-server-errors" role="alert">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        @csrf

                            <input type="hidden" name="registration_fill_time_ms" value="">
                            <input type="hidden" name="registration_keypress_count" value="0">
                            <input type="hidden" name="registration_paste_insert_count" value="0">
                            <input type="hidden" name="registration_browser_autofill" value="0">

                            <div class="registration-customfield" aria-hidden="true">
                                <label for="contact_url">Не заполняйте это поле</label>
                                <input type="text"
                                       id="contact_url"
                                       name="contact_url"
                                       value=""
                                       tabindex="-1"
                                       autocomplete="off">
                            </div>

                            <div class="form-field {{ $errors->has('username') ? 'error' : '' }}">
                                <input type="text"
                                       name="username"
                                       class=" required_input unique_username auth-input mb-2"
                                       value="{{ old('username') }}"
                                       placeholder="Никнейм (Имя) *"
                                       role="presentation"
                                       autocomplete="new-nickname"
                                       required>
                                <div class="check"></div>
                                <small>{{ $errors->first('username') }}</small>
                            </div>

                            <div class="form-field {{ $errors->has('email') ? 'error' : '' }}">

                                <input type="text"
                                       name="email"
                                       class=" required_input unique_email emailField auth-input mb-2"
                                       placeholder="E-mail *"
                                       value="{{ old('email') }}"
                                       placeholder="example@email.ru" autocomplete="new-email" required>
                                <small>{{ $errors->first('email') }}</small>
                            </div>

                            <div class="codeBlock" style="display: {{ old('email') ? 'block' : 'none' }};">
                                <div class="email-code-row mb-2">
                                    <div class="form-field email-code-field {{ $errors->has('code') ? 'error' : '' }}">
                                        <input type="text"
                                               name="code"
                                               class="required_input auth-input mb-2 emailCodeField"
                                               value="{{ old('code') }}"
                                               maxlength="6"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               placeholder="Код подтверждения почты *"
                                               required>
                                        <div class="check"></div>
                                        <small>{{ $errors->first('code') }}</small>
                                    </div>
                                    <button type="button" class="btn btn_fill sendCodeBtn">Отправить письмо с кодом</button>
                                </div>
                                <small class="email-code-status" aria-live="polite"></small>
                            </div>

                            <div class="form-field {{ $errors->has('phone') ? 'error' : '' }}">
                                <input type="text"
                                       name="phone"
                                       class="phone-mask auth-input mb-2"
                                       value="{{ old('phone') }}"
                                       placeholder="Телефон"
                                       role="presentation"
                                       autocomplete="new-phone"
                                       >
                                <div class="check"></div>
                                <small>{{ $errors->first('phone') }}</small>
                            </div>



                            <div class="form-field {{ $errors->has('password') ? 'error' : '' }}">
                                <input type="password"
                                       name="password"
                                       value="{{old('password')}}"
                                       class=" required_input auth-input mb-2"
                                       data-min="6"
                                       data-min-error="Количество символов в поле Пароль должно быть не менее 6."
                                       autocomplete="new-password"
                                       placeholder="Придумайте пароль *"
                                       required>
                                <div class="check"></div>
                                <small>{{ $errors->first('password') }}</small>
                            </div>

                            <div class="form-field {{ $errors->has('password_confirmation') ? 'error' : '' }}">
                                <input type="password"
                                       name="password_confirmation"
                                       value="{{old('password_confirmation')}}"
                                       class=" required_input confirmed_input auth-input mb-2"
                                       data-confirmed="password"
                                       data-min="6"
                                       data-min-error="Количество символов в поле Подтверждение пароля должно быть не менее 6."
                                       autocomplete="new-password-confirm"
                                       placeholder="@lang('app.confirm_password') *"
                                       required
                                >
                                <div class="check"></div>
                                <small>{{ $errors->first('password_confirmation') }}</small>
                            </div>

                            <br><br>
                            * — Обязательное поле
                            <br><br>
                            <div class="form__agree" style=" margin-top: 10px">
                                <input type="checkbox" id="checkbox4" name="agree_1" class="form__checkbox" value="checkbox" required {{old('agree_1') ? 'checked' : ''}}>
                                <label for="checkbox4" style="margin-top: 0;margin-right: 5px;">
                                    Я согласен с условиями
                                    <a style="text-decoration: underline" href="/docs/privacy_policy.docx" download>положения о конфиденциальности</a>
                                </label>
                                @if($errors->has('agree_1'))
                                    <div class="error" style="color: red;">(!)</div>
                                @endif
                            </div>
                            <br>
                            <div class="form__agree" style=" margin-top: 10px">
                                <input type="checkbox" id="checkbox7" name="agree_4" class="form__checkbox" value="checkbox" required {{old('agree_4') ? 'checked' : ''}}>
                                <label for="checkbox7" style="margin-top: 0;margin-right: 5px;">
                                    Я даю свое согласие на
                                    <a href="/docs/processing_of_personal_data.docx" download style="text-decoration: underline">обработку персональных данных</a>
                                </label>
                                @if($errors->has('agree_4'))
                                    <div class="error" style="color: red;">(!)</div>
                                @endif
                            </div>
                            <br>
                            <div class="form__agree" style="display: flex; align-items: center; margin-top: 10px">
                                <input type="checkbox" id="checkbox5" name="agree_2" class="form__checkbox" value="checkbox" required {{old('agree_2') ? 'checked' : ''}}>
                                <label for="checkbox5" style="margin-top: 0;margin-right: 5px;">Я согласен с <a style="padding-left: 5px;text-decoration: underline" href="/docs/license.docx"
                                                                                                                download>условиями Лицензионного
                                        соглашения</a></label>
                                @if($errors->has('agree_2'))
                                    <div class="error" style="color: red;">(!)</div>
                                @endif
                            </div>
                            <br>
                            <div class="form__agree" style="display: flex; align-items: center; margin-top: 10px">
                                <input type="checkbox" id="checkbox6" name="agree_3"  class="form__checkbox" value="checkbox" required {{old('agree_3') ? 'checked' : ''}}>
                                <label for="checkbox6" style="margin-top: 0;margin-right: 5px;">
                                        Я согласен с <a style="padding-left: 5px;text-decoration: underline" href="/docs/rules.docx" download>правилами
                                        сервиса</a></label>
                                @if($errors->has('agree_3'))
                                    <div class="error" style="color: red;">(!)</div>
                                @endif
                            </div>
                            <br>

                        <div class="login__btns d-flex mb-4">
                            <button class="btn btn_fill login__btn-big" type="submit">Зарегистрироваться</button>
                            <a href="/login" class="start__btn btn start__btn-big login__btn-small" >Войти</a>
                        </div>                    </form>
                    <a href="{{ route('password.request') }}" class="login__reset-link"></a>
                    @include('auth.socials_light')
                </div>
            </div>
        </section>
        <section class="advantages">
            <div class="container">
                <ul class="advantages__list">
                    <li class="advantages__item txt-neon-white">Уже 60 000+ участников </li>
                    <li class="advantages__item txt-neon-white">Опубликовано 350+ видео и 60 + челленджей</li>
                    <li class="advantages__item text-accent">Deels - новая развлекательная социальная сеть</li>
                </ul>
            </div>
        </section>
    </main>
@endsection

@if(get_option('enable_recaptcha_registration') == 1)
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif


@section('page-js')
    <script src="{{ext_asset('/dist/js/validations.js')}}"></script>
    <script>
        (() => {
            const form = document.querySelector('.sign__form');
            const trackedInputs = Array.from(form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([name="contact_url"])'));
            const fillTimeField = form.querySelector('[name="registration_fill_time_ms"]');
            const keypressField = form.querySelector('[name="registration_keypress_count"]');
            const pasteInsertField = form.querySelector('[name="registration_paste_insert_count"]');
            const autofillField = form.querySelector('[name="registration_browser_autofill"]');
            let fillingStartedAt = null;
            let keypressCount = 0;
            let pasteInsertCount = 0;

            const markAutofill = () => {
                autofillField.value = '1';
            };

            const hasAutofilledInput = () => {
                try {
                    return trackedInputs.some((input) => input.matches(':-webkit-autofill'));
                } catch (error) {
                    return false;
                }
            };

            trackedInputs.forEach((input) => {
                input.addEventListener('focus', () => {
                    if (fillingStartedAt === null) fillingStartedAt = performance.now();
                }, {once: true});

                input.addEventListener('keydown', (event) => {
                    keypressField.value = String(++keypressCount);
                    if (event.key === 'Insert') {
                        pasteInsertField.value = String(++pasteInsertCount);
                    }
                });

                input.addEventListener('paste', () => {
                    pasteInsertField.value = String(++pasteInsertCount);
                });

                input.addEventListener('animationstart', (event) => {
                    if (event.animationName === 'registrationAutofill') markAutofill();
                });
            });

            form.addEventListener('submit', () => {
                if (fillingStartedAt !== null) {
                    fillTimeField.value = String(Math.round(performance.now() - fillingStartedAt));
                }
                if (hasAutofilledInput()) markAutofill();
            });

            const emailField = document.querySelector('.emailField');
            const codeBlock = document.querySelector('.codeBlock');
            const codeField = document.querySelector('.emailCodeField');
            const sendButton = document.querySelector('.sendCodeBtn');
            const status = document.querySelector('.email-code-status');
            const customField = document.querySelector('input[name="contact_url"]');
            const csrfToken = document.querySelector('input[name="_token"]').value;
            let timerId = null;

            const setStatus = (message, isError = false) => {
                status.textContent = message;
                status.classList.toggle('email-code-status_error', isError);
            };

            const startTimer = (seconds = 120) => {
                clearInterval(timerId);
                sendButton.disabled = true;

                const render = () => {
                    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                    const rest = String(seconds % 60).padStart(2, '0');
                    sendButton.innerHTML = `Отправить повторно <span class="send-code-timer">${minutes}:${rest}</span>`;

                    if (seconds-- <= 0) {
                        clearInterval(timerId);
                        timerId = null;
                        sendButton.disabled = false;
                        sendButton.textContent = 'Отправить письмо с кодом';
                    }
                };

                render();
                timerId = setInterval(render, 1000);
            };

            emailField.addEventListener('blur', async () => {
                const email = emailField.value.trim();
                const availability = validateEmail(email) ? await checkInDatabase(email) : null;
                if (availability?.email_exists) {
                    codeBlock.style.display = 'block';
                }
            });

            emailField.addEventListener('input', () => {
                clearInterval(timerId);
                timerId = null;
                sendButton.disabled = false;
                sendButton.textContent = 'Отправить письмо с кодом';
                codeField.value = '';
                setStatus('');
            });

            sendButton.addEventListener('click', async () => {
                const email = emailField.value.trim();
                sendButton.disabled = true;
                setStatus('Отправляем письмо…');

                try {
                    const response = await fetch('/user/sendEmailCode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({email, contact_url: customField.value}),
                    });
                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        if (result.banned) {
                            window.location.href = '/banned';
                            return;
                        }
                        if (result.retry_after) startTimer(result.retry_after);
                        throw new Error(result.error || 'Не удалось отправить письмо.');
                    }

                    setStatus('Письмо с кодом отправлено.');
                    startTimer(120);
                } catch (error) {
                    setStatus(error.message, true);
                    if (!timerId) sendButton.disabled = false;
                }
            });

            codeField.addEventListener('blur', async () => {
                const code = codeField.value.trim();
                if (!/^\d{6}$/.test(code)) {
                    setStatus('Введите шестизначный код.', true);
                    return;
                }

                const response = await fetch('/user/checkEmailCode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({email: emailField.value.trim(), code}),
                });
                const result = await response.json();
                setStatus(result.valid ? 'Код подтверждён.' : 'Неверный или просроченный код.', !result.valid);
            });
        })();
    </script>
@endsection
@section('page-css')
    <style>
        @keyframes registrationAutofill {
            from { opacity: 0.99; }
            to { opacity: 1; }
        }

        .sign__form input:-webkit-autofill {
            animation-name: registrationAutofill;
            animation-duration: 0.01s;
        }

        .campaign-form__field {
            margin: 7px 0 !important;
        }

        .campaign-form__label {
            margin-top: 30px;
        }

        .registration-customfield {
            position: absolute !important;
            left: -10000px !important;
            top: auto !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .codeBlock {
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
        }

        .email-code-row {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .email-code-field {
            width: 100%;
            min-width: 0;
            max-width: none;
        }

        .email-code-field .auth-input {
            width: 100% !important;
            min-width: 0;
            max-width: 100% !important;
            box-sizing: border-box;
        }

        .sendCodeBtn {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            min-height: 48px;
            padding-left: 14px !important;
            padding-right: 14px !important;
            white-space: normal;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
            box-sizing: border-box;
        }

        .send-code-timer {
            display: inline-block;
            width: 5ch;
            white-space: nowrap;
            text-align: left;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        .email-code-status {
            margin: 8px 0 15px;
            display: block;
            color: #78b13f;
        }

        .email-code-status_error {
            color: red;
        }

        @media(max-width: 574px) {
            .email-code-row {
                display: block;
            }

            .sendCodeBtn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection

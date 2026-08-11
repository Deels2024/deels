@extends('layouts.neon.app_light')
@section('title')
    {{isset($title) && $title ? $title : 'Авторизация'}}
@endsection
@push('meta-data')
<meta name="robots" content="noindex, nofollow" />
<meta name="description" content="{{$description ?? ''}}">
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
            /*height: auto!important;*/
            max-width: 540px!important;
            background-color: transparent!important;
            border: 1px solid #D9D9D9!important;
            color: white!important;
            font-family: "Gilroy", sans-serif!important;
        }
        .auth-input::placeholder {
            color: white!important;
            font-size: 16px!important;
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
            bottom: 260px;
            background-color: #4086b9;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            padding: 21px 21px 0 14px;
            z-index: 999;

        }
        .popup-try__text{
            font-size: 30px;
            max-width: 250px;
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
        @media(max-height:940px){
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
        @media(max-height:876px){
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
        }
        @media(max-width: 767px){
            .login__container{
                flex-direction: column-reverse;
                align-items: center;
            }
            .login__left img{
                max-width: 135px;
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
        }
        @media(min-width:575px){
            .login__text .text-accent{
                color: white;
            }
        }


    </style>

    <main>
        <section class="login">
            <div class="container login__container d-flex">
                <div class="login__left">
                    <img loading="lazy" src="/dist/images/pig-big.png" alt="копилка" class="login__img">
                </div>
                <div class="login__right">
                    <h2 class="login__title txt-neon-white mb-5">Войти</h2>
                    <p class="login__text txt-neon-white mb-5">
                        <span class="mobile-border-bottom mb-1"><span class="text-accent">Публикуй мемы и видео</span> - зарабатывай на контенте;</span><br>
                        <span class="mobile-border-bottom mb-1">Челленджи c <span class="text-accent">реальными призами;</span></span><br>
                        <span class="mobile-border-bottom mb-1">Деньги за контент <span class="text-accent">без алгоритмических мучений.</span></span>
                    </p>
                    <form method="POST" action="{{ route('login', request()->all()) }}" class="login__form">
                        {{ csrf_field() }}
                        @if($errors->has('email'))
{{--                            <div class="alert-danger mb-2">--}}
{{--                                {{ $errors->first('email')}}--}}
{{--                            </div>--}}
                        @endif
                        <input class="auth-input mb-2" type="text" name="email" id="email" placeholder="E-mail или никнейм">
                        @if($errors->has('password'))
{{--                            <div class="alert-danger ml-2">--}}
{{--                                {{ $errors->first('password')}}--}}
{{--                            </div>--}}
                        @endif
                        <input class="auth-input mb-40" type="password" name="password" id="password" placeholder="Пароль">
                        @if(get_option('enable_recaptcha_login') == 1)
                            <div class="form-group row  {{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                                <div class="col-md-6 offset-md-4">
                                    <div class="g-recaptcha" data-sitekey="{{get_option('recaptcha_site_key')}}"></div>
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="help-block text-danger">
                                                <strong>{{ str_replace('g-recaptcha-response', 'reCAPTCHA', $errors->first('g-recaptcha-response')) }}</strong>
                                            </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="login__btns mb-4 d-flex">
                            <button class="btn btn_fill login__btn-small" type="submit" onclick="$(this).parents('form').submit()">Войти</button>
                            <a href="{{route('register')}}" class="start__btn btn start__btn-big login__btn-big">Зарегистрироваться</a>
                        </div>
                        <a class="link-forget" href="{{ route('password.request') }}">Забыли пароль?</a>
                    </form>
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

@section('page-js')
    @if(get_option('enable_recaptcha_login') == 1)
        <script src='https://www.google.com/recaptcha/api.js'></script>
    @endif
@endsection

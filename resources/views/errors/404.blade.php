@extends('layouts.neon.app')
@section('title') {{ $pageTitle ?? trans('app.not_found_404') }} | @parent @endsection

@section('content')
    <style>
        .error {
            background: url('/dist/error-bg.jpg') no-repeat 20% /cover;
            width: 100vw;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 55%;
            justify-content: space-between;
            gap: 15px;
        }

        .error__top {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .error__icon {
            margin-bottom: 50px;
        }
        .error__title {
            text-transform: uppercase;
            font-weight: 800;
        }

        .error__suptitle {
            font-family: 'Gilroy', sans-serif;
            margin-bottom: 15px;
            font-weight: 500;
            font-size: 16px;
            line-height: 19px;
            letter-spacing: 0.01em;
        }
    </style>
    <div class="error">
        <div class="error__content">
            <div class="error__top">
                <div class="error__icon">
                    <img src="/dist/icon.png" alt="">
                </div>
                <p class="error__suptitle">
                    {{ $errorSuptitle ?? 'Упс... Что-то пошло не так' }}
                </p>
                <h2 class="error__title">{{ $errorTitle ?? '404 ошибка страницы' }}</h2>
            </div>

            @if($showReturnLink ?? true)
                <a href="/" class="btn text btn_fill ">
                    Вернуться на сайт
                </a>
            @endif
        </div>
    </div>

@endsection

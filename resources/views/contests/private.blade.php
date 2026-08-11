@extends('layouts.neon.app')
@php
    $isBattle = ($contest ?? null) instanceof \App\Models\Battle;
    $contestName = $isBattle ? 'батл' : 'челлендж';
    $contestNameTitle = $isBattle ? 'Батл' : 'Челлендж';
    $contestNameGenitive = $isBattle ? 'батла' : 'челленджа';
@endphp
@section('title') {{ $contestNameTitle }} недоступен @parent @endsection

@section('content')
    <style>
        .error {
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
                    Кажется, автор {{ $contestNameGenitive }} решил оставить его в секрете. Вы можете обратиться к нему лично для изменения настроек видимости этого {{ $contestNameGenitive }}.
                </p>
                <p class="error__suptitle">
                    Чтобы поучаствовать в {{ $contestName }}е без доступа к нему, нужно получить приглашение создателя {{ $contestNameGenitive }}.
                </p>

            </div>

            @if($showReturnLink ?? true)
                <a href="/" class="btn text btn_fill ">
                    Вернуться на сайт
                </a>
            @endif
        </div>
    </div>

@endsection

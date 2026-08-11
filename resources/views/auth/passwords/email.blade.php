@extends('layouts.neon.app')
@section('title')
    Восстановление пароля
@endsection
@section('content')
    <div class="background__dark"></div>
    <div class="sign">
        <div class="container">
            <h2>Восстановление <br> пароля
                <span>Восстановление <br> пароля</span>
            </h2>
            <form class="form-horizontal form sign__form" role="form" method="POST" action="{{ route('password.email') }}">
                {{ csrf_field() }}

                <label for="email">Эл. Почта</label>
                <input type="text" name="email" id="email" placeholder="Введите email">
                <div class="form__btns">
                    <div class="form__btn sign__recovery btn btn_fill" onclick="$(this).parents('form').submit()">Восстановить
                        пароль
                    </div>
                </div>
            </form>
            <div class="sign__message animate__animated animate__fadeInUp">
                На указанную почту отправлено письмо с инструкциями для восстановление пароля
            </div>
        </div>
    </div>
@endsection

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
            @include('admin.flash_msg')
            @if(session()->has('errors'))
                <div style="padding: 20px;background-color: rgb(199,23,23);margin-bottom: 20px;color: #fff;">
                    <b>Ошибка!</b> Произошла ошибка при смене пароля, проверьте корректность введенных данных, при повторении проблемы, обратитесь в службу поддержки.
                </div>
            @endif
            <form class="form-horizontal form sign__form" role="form" method="POST" action="{{ route('password.update') }}">
                {{ csrf_field() }}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-field">
                    <label class="campaign-form__label">@lang('app.email_address')</label>
                    <input type="text" name="email"
                           class="campaign-form__field field required_input unique_username"
                           value="{{ $email }}"
                           placeholder="example@email.ru"
                           required>
                    @if($errors->has('email'))
                        <div class="error" style="color: red;margin-bottom: 10px">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="form-field">
                    <label class="campaign-form__label">@lang('app.password')</label>
                    <input type="password" name="password"
                           class="campaign-form__field field required_input unique_username"
                           required>
                    @if($errors->has('password'))
                        <div class="error" style="color: red;margin-bottom: 10px">{{ $errors->first('password') }}</div>
                    @endif
                </div>


                <div class="form-field">
                    <label class="campaign-form__label">Подтвердите пароль</label>
                    <input type="password" name="password_confirmation"
                           class="campaign-form__field field required_input unique_username"
                           required>
                    @if($errors->has('password_confirmation'))
                        <div class="error" style="color: red;margin-bottom: 10px">{{ $errors->first('password_confirmation') }}</div>
                    @endif
                </div>

                <label for="">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn">
                            Сменить пароль
                        </button>
                    </div>
                </label>
            </form>
        </div>
    </div>
@endsection

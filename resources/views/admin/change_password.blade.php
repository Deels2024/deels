@extends('layouts.admin.app_neon')

@section('title') @if(! empty($title)) {{$title}} @endif  @parent @endsection

@section('content')

    <div class="account__content password">
        <h2 class="account__title account__title-pos">
            Сменить пароль
        </h2>
        <form action="" method="post" class="password__form">
            @csrf
            <div class="flex">
                <div class="password__new">
                    <label for="new" class="new__label">Новый пароль</label>
                    <input id="new" class="new__input" name="new_password" type="password" placeholder="Введите новый пароль">
                </div>
                <div class="password__new">
                    <label for="repeat" class="new__label">Повторите пароль</label>
                    <input id="repeat" class="new__input" name="new_password_confirmation" type="password" placeholder="Введите еще раз новый пароль">
                </div>
            </div>
            <div class="password__new">
                <label for="old" class="new__label">Текущий пароль</label>
                <input id="old" class="new__input" name="old_password"  type="password" placeholder="Укажите Ваш текущий пароль">
            </div>
            <div class="btn btn_fill profile__btn" onclick="$(this).parent().submit()">Сохранить мои данные</div>
        </form>
    </div>

@endsection

@section('page-js')

@endsection

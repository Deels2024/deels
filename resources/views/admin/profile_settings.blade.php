@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')
    <style>
        .check {
            display: none;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: rotate(45deg);
            height: 16px;
            width: 8px;
            margin-top: -10px;
            border-bottom: 3px solid #78b13f;
            border-right: 3px solid #78b13f;
        }
        .valid {
            position: relative;
        }
        .valid .check {

            display: block;
        }
        .mb-0 {
            margin-bottom: 0!important;
        }
        .privacy-radio {
            display: flex!important;
            align-items: center!important;
            gap: 10px;
            cursor: pointer;
            line-height: 20px;
        }
        .privacy-radio + .privacy-radio {
            margin-top: 10px;
        }
        .privacy-radio input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .privacy-radio__control {
            width: 18px;
            height: 18px;
            min-width: 18px;
            margin-top: 1px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            position: relative;
            display: inline-block;
        }
        .privacy-radio input:checked + .privacy-radio__control {
            border-color: #b224ef;
        }
        .privacy-radio input:checked + .privacy-radio__control::after {
            content: '';
            position: absolute;
            inset: 3px;
            border-radius: 50%;
            background: #b224ef;
        }
    </style>
    <div class="account__content profile">
        <h2 class="account__title account__title-pos">Настройки профиля</h2>
        <form action="{{route('profile_settings_save')}}" method="post" class="form validated_form profile__form"
              enctype="multipart/form-data">
            @csrf
            <div class="flex">
                <div class="profile__login">
                    <label for="login" class="new__label">Логин</label>
                    <input
                            id="login"
                            class="new__input mb-0"
                            type="text"
                            name="email"
                            value="{{old('email',$user->email)}}"
                    />
                </div>


                <div class="profile__id">
                    <label for="id" class="new__label"></label>
                    <input
                            id="id"
                            class="new__input mb-0"
                            type="hidden"
                            name="id"
                            placeholder="1234@gmail.com"
                            value="{{old('id',$user->id)}}"
                    />
                </div>


                <div class="profile__address">
                    <label for="address" class="new__label">Адрес</label>
                    <input
                            id="address"
                            class="new__input mb-0"
                            type="text"
                            name="address"
                            placeholder="Укажите ваш адрес"
                            value="{{old('address',$user->address)}}"
                    />
                </div>



            </div>


            <div class="flex align_start">
                <div class="form-field profile__login" style="position:relative;">
                    <label for="login" class="new__label">Ник</label>
                    <input
                            id="username"
                            class="new__input required_input unique_username mb-0"
                            type="text"
                            name="username"
                            value="{{old('username',$user->username)}}"
                    />
                    <div class="check"></div>
{{--                    <small style="position: absolute; bottom: 0"></small>--}}
                    <small class=""></small>
                </div>

                <div class="profile__name">
                    <label for="name" class="new__label">Имя</label>
                    <input
                            id="name"
                            class="new__input mb-0"
                            type="text"
                            placeholder="Семен"
                            name="name"
                            value="{{old('name',$user->name)}}"
                    />
                </div>
            </div>
            <div class="flex">
                <div class="profile__phone">
                    <label for="phone" class="new__label">Телефон</label>
                    <input
                            id="phone"
                            class="new__input mb-0"
                            name="phone"
                            type="text"
                            value="{{old('phone',$user->phone)}}"
                            placeholder="Укажите ваш телефон"
                            @if($user->phone)
                                disabled readonly
                                @endif
                    />
                </div>
            </div>
            <div class="flex">
                <div class="profile__city">
                    <div class="new__label">Страна</div>
                    <select id="country_id" name="country_id" class=" mb-0">
                        @foreach($countries as $country)
                            <option value={{$country->id}} {{ old('country_id', $user->country_id) == $country->id  ? 'selected' : '' }}> {{$country->name_ru}} </option>
                            @if(old('country_id',$user->country_id)== $country->id )
                                class="active_option"
                            @endif>{{$country->name_ru}}</option>
                        @endforeach
                    </select>

                </div>
                <div class="profile__gender">
                    <div class="new__label">Пол</div>
                    <select id="gender" name="gender" class=" mb-0">
                        <option value="male" {{ old('gender', $user->gender) == "male" ? 'selected' : '' }}>
                            Мужчина
                        </option>
                        <option value="female" {{ old('gender', $user->gender) == "female" ? 'selected' : '' }}>
                            Женщина
                        </option>
                    </select>
                </div>

            </div>
            <div class="flex">
                <div class="new__description form-field" style="width: 100%">
                    <label for="status" class="new__label">Статус</label>
                    <textarea minlength="0"
                              class="new__input stories_description"
                              name="status"
                              id="status"
                              rows="2"
                              placeholder="Введите текст...">{{old('status',$user->status)}}</textarea>
                    <small></small>
                </div>
            </div>
            <div class="flex">
                <div class="new__description form-field" style="width: 100%;">
                    <label for="first_message_followings_only" class="privacy-radio">
                        <input type="radio" value="1" id="first_message_followings_only" name="first_message_followings_only" {{ old('first_message_followings_only', $user->first_message_followings_only) ? 'checked="checked"': '' }}>
                        <span class="privacy-radio__control"></span>
                        <span>Написать первое сообщение мне могут только пользователи, на которых я подписан(а)</span>
                    </label>
                    <label for="first_message_followings_all" class="privacy-radio">
                        <input type="radio" value="0" id="first_message_followings_all" name="first_message_followings_only" {{ old('first_message_followings_only', $user->first_message_followings_only) ? '': 'checked="checked"' }}>
                        <span class="privacy-radio__control"></span>
                        <span>Написать первое сообщение мне могут все пользователи</span>
                    </label>
                </div>
            </div>
            @if(!auth()->user()->telegram_id)
                <div class="profile__phone">
                    <label for="phone" class="new__label">Код для подключения telegram-бота</label>
                </div>
            @else
{{--                <div class="profile__phone">--}}
{{--                    <label for="phone" class="new__label">Код для отключения telegram-бота</label>--}}
{{--                </div>--}}
            @endif

            <div class="flex">
                @php
                    $connect_url = '';
                    $connect_url = 'https://t.me/'.env('TG_BOT_LOGIN', 'kopiberi_bot').'?start=';
                @endphp

                <div class="profile-reflink"  style="margin-bottom: 20px">
                    @if(auth()->user()->telegram_id)
                        <div class="profile-reflink__texts">
                            Вы подключены к telegram-боту  <a href="{{$connect_url}}" target="_blank">{{'@'.env('TG_BOT_LOGIN', 'kopiberi_bot')}}</a></div>@else
                        <div class="profile-reflink__text copy-text" data-text="Код скопирован. Отправьте его боту в ответном сообщении">
                            {{auth()->user()->connect_token}}</div>
                        <a href="{{$connect_url}}{{auth()->user()->connect_token}}" class="btn_fill profile-reflink__btn d-flex align-items-center">Подключить бот</a>
                    @endif

                </div>


            </div>

            <div class="account-info__dignity">
                <div class="profile-dignity bg-dark" style="padding: 20px">
                    <div class="profile-dignity__content" style="width: 100%;">
                        <p class="profile-dignity__text" style="width: 100%;">Внимание! В настоящий момент telegram-бот используется для удобства пополнения счета.</p>
                    </div>
                </div>
            </div>

            @if(auth()->user()->telegram_id)

            <div class="flex">
                <div class="new__description form-field" style="width: 100%;">
                    <label for="telegram_notify" class="checkbox-inline" style="display: flex; align-items: center">
                        <input type="checkbox" value="1" id="telegram_notify" name="telegram_notify" {{ auth()->user()->telegram_notify ? 'checked="checked"': '' }} style="margin-right: 5px">
                        Получать уведомления в telegram-бот
                    </label>
                </div>
            </div>

            @endif

            <div class="btn btn_fill profile__btn" onclick="$(this).parent().submit()" style="margin-bottom: 0; margin-top: 20px">
                Сохранить мои данные
            </div>

        </form>
        <form action="{{route('profile.delete')}}" method="post" class="profile__form"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="email" value="{{$user->email}}">
            <input type="hidden" name="name" value="{{$user->fullname}}">
            <input type="hidden" name="account_delete" value="1">
        <button class="btn btn_fill profile__btn" type="submit" style="background: #ff0000; margin-top: 0">
            Удалить аккаунт
        </button>
        </form>
    </div>
@endsection

@section('page-js')
    <script src="{{ext_asset('/dist/js/validations.js')}}"></script>
    <script>
        function setActiveCity() {
            $(".active_option").click();
            $(".banks__open-active").click();
        }

        setActiveCity()
    </script>
@endsection

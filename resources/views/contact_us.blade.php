@extends('layouts.neon.app')
@section('title')Связь и контакты@endsection
@section('meta-data')
    <meta name="description" content="Узнать контакты и связаться с кураторами проекта">
@endsection

@section('content')
    <div class="background__dark"></div>
    <div class="contact">
        <div class="container">
            <h1>Контакты
                <span>Контакты</span>
            </h1>
            <div class="contact__info flex flex_start">
                <div class="map">
                    <div id="map"></div>
                    <a href="https://www.google.com/maps/search/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3,+%D0%BF%D1%80.+%D0%92%D0%B5%D1%82%D0%B5%D1%80%D0%B0%D0%BD%D0%BE%D0%B2+166.+%D0%BB.%D0%90/@59.8345372,30.1407306,19z" class="contact__map-btn">Открыть на карте</a>
                </div>
                <div class="contact__items">
                    <div class="contact__item">
                        <img class="contact__icon" src="/dist/images/icons/phone_color.svg">
                        <a href="tel:+78125079808">+7 (812) 5079808</a>
                    </div>
                    <div class="contact__item">
                        <img class="contact__icon" src="/dist/images/icons/mail_color.svg">
                        <a href="mailto:info@deels.ru">info@deels.ru
                        </a>
                    </div>
                    <div class="contact__item">
                        <img class="contact__icon" src="/dist/images/icons/map.png">
                        <a href="https://www.google.com/maps/search/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3,+%D0%BF%D1%80.+%D0%92%D0%B5%D1%82%D0%B5%D1%80%D0%B0%D0%BD%D0%BE%D0%B2+166.+%D0%BB.%D0%90/@59.8345372,30.1407306,19z">Санкт-Петербург, пр. Ветеранов 166. л.А
                        </a>
                    </div>
                </div>
            </div>
            <div class="contact__questions">
                Есть вопросы или предложения? Свяжитесь с нами любым удобным для вас способом.
            </div>

            <form action="{{ route('contact_us') }}" class="contact__form form" method="POST">
                {{ csrf_field() }}
                @if ($errors->any())
                    <div style="margin-bottom: 30px">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li style="color: red;margin-bottom: 10px">{{$error}}</li>
                            @endforeach
                        </ul>

                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 30px">
                        {!! session('error') !!}
                    </div>
                @endif
                @if(session('success'))
                <div class="" style="margin-bottom: 30px">
                    {!! session('success') !!}
                </div>
                @endif
                <label for="name">Ваше имя *</label>
                <input type="text" name="name" id="name" placeholder="Введите имя" value="{{old('name')}}">
                <input type="text" name="lastname" id="lastname" placeholder="Введите фамилию" value="{{old('lastname')}}" tabindex="-1" style="position: absolute;width: 1px;height: 1px;overflow: hidden;bottom: 0;right:0;z-index: -999;padding: 0; margin: 0; border: none;">
                <label for="email">Эл. Почта *</label>
                <input type="text" name="email" id="email" placeholder="1234@gmail.com" value="{{old('email')}}">
                <label for="phone">Номер телефона</label>
                <input type="tel" name="phone" class="phone-mask" id="phone" placeholder="+7 (999) 000-00-00" inputmode="numeric" value="{{old('phone')}}">
                <label for="message">Ваше сообщение *</label>
                <textarea name="message" id="message" cols="30" rows="10">{{old('message')}}</textarea>
                <div class="contact__send flex">
                    <div class="contact__btn btn btn_fill" onclick="$(this).parents('form').submit()">Отправить</div>
                    <div class="contact__agree">
                        <input class="contact__checkbox" id="checkbox" type="checkbox" name="agreement" {{old('agreement') ? 'checked' : ''}}>
                        <label for="checkbox">Я даю свое согласие на <a href="/docs/processing_of_personal_data.docx" download style="padding-left: 5px; text-decoration: underline">обработку персональных данных</a></label>
                    </div>
                </div>
            </form>
            <div class="contact__list">
                <ul>
                    <li>
                        <div>ИНН</div>
                        <div></div>
                        <div>7807396346</div>
                    </li>
                    <li>
                        <div>КПП</div>
                        <div></div>
                        <div>780701001</div>
                    </li>
                    <li>
                        <div>ОГРН</div>
                        <div></div>
                        <div>1147847408235</div>
                    </li>
                    <li>
                        <div>ОКПО</div>
                        <div></div>
                        <div>74857756</div>
                    </li>
                    <li>
                        <div>Расчетный счет</div>
                        <div></div>
                        <div>40702810755240005617</div>
                    </li>
                    <li>
                        <div>Банк</div>
                        <div></div>
                        <div>СЕВЕРО-ЗАПАДНЫЙ БАНК ПАО СБЕРБАНК</div>
                    </li>
                    <li>
                        <div>БИК</div>
                        <div></div>
                        <div>044030653</div>
                    </li>
                    <li>
                        <div>Корр.счет</div>
                        <div></div>
                        <div>30101810500000000653</div>
                    </li>
                    <li>
                        <div>Юридический адрес</div>
                        <div></div>
                        <div>198264, Санкт-Петербург г, Ветеранов пр-кт, дом № 166, литера А</div>
                    </li>
                    <li>
                        <div>Телефон</div>
                        <div></div>
                        <div>+7 (812) 507-98-08</div>
                    </li>
                    <li>
                        <div>Генеральный директор</div>
                        <div></div>
                        <div>Серебряков Сергей Николаевич</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA39d05VoOv5c7SilmGPVC2_pjSrKZ0D84" type="text/javascript"></script>
@endsection

@if(get_option('enable_recaptcha_contact_form') == 1)
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif

@if(!isset($hide_footer))
    <footer class="footer">
        <div class="container footer__menu">
            <a href="{{route('home')}}" class="footer__logo">
                <img src="/dist/images/icons/deels.svg" alt="DEELS"/>
            </a>
            <div class="footer__list">
                <ul>
                    <li @if(\Illuminate\Support\Facades\Route::currentRouteName()==='home') class="active" @endif>
                        <a href="/">Главная</a>
                    </li>

                    @php
                        $header_menu_pages = \App\Models\Post::whereStatus(1)->where('show_in_header_menu', 1)->get();
                    @endphp
                    @if($header_menu_pages->count() > 0)
                        @foreach($header_menu_pages as $page)
                            <li @if(\Illuminate\Support\Facades\Route::currentRouteName()==='single_page') class="active" @endif>
                                <a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a>
                            </li>
                        @endforeach
                    @endif

                    <li @if(request()->routeIs('challenges.catalog', 'deels.public.challenges.show')) class="active" @endif>
                        <a href="{{ route('challenges.catalog') }}">Челленджи</a>
                    </li>
                    <li @if(request()->routeIs('deels.public.battles.*', 'battle_page')) class="active" @endif>
                        <a href="{{ route('deels.public.battles.index') }}">Баттлы</a>
                    </li>
                    <li @if(request()->routeIs('stories.catalog', 'deels.public.stories.show')) class="active" @endif>
                        <a href="{{ route('stories.catalog', ['type' => 'popular']) }}">Истории</a>
                    </li>
                    <li @if(request()->routeIs('deels.public.campaigns.*', 'browse_campaign', 'campaign_single')) class="active" @endif>
                        <a href="{{ route('deels.public.campaigns.index') }}">Копилки</a>
                    </li>
                    <li @if(\Illuminate\Support\Facades\Route::currentRouteName()==='contact_us') class="active" @endif>
                        <a href="{{route('contact_us')}}">@lang('app.contact_us')</a>
                    </li>
                    <li @if(\Illuminate\Support\Facades\Route::currentRouteName()==='card_pay') class="active" @endif>
                        <a href="{{route('offer')}}">Правила и оферты</a>
                    </li>
                </ul>
            </div>
            <div class="footer__icons">
                <a target="_blank" href="https://t.me/deels_ru">
                    <img src="/images/action-top-banner/tg.svg" alt="Telegram">
                </a>
                <a target="_blank" href="https://vk.com/deels">
                    <img src="/images/action-top-banner/vk.svg" alt="VK">
                </a>
            </div>
        </div>
        <div class="container footer__items">
            <div class="footer__item">
                <img src="/dist/images/icons/map.svg"/>
                <a href="/contact-us">Санкт-Петербург, пр. Ветеранов 166, лит. А</a>
            </div>
            <div class="footer__item">
                <img src="/dist/images/icons/phone.svg"/>
                <a href="tel:+78125079808">+7 (812) 5079808</a>
            </div>
            <div class="footer__item">
                <img src="/dist/images/icons/mail.svg"/>
                <a href="mailto:info@deels.ru">info@deels.ru</a>
            </div>
        </div>
        <div class="container footer__href">
            <a href="/docs/privacy_policy.docx">Политика
                конфиденциальности ({{toMb(filesize(public_path('/docs/file3.docx')))}}
                /{{pathinfo(public_path('/docs/privacy_policy.docx'))['extension']}})</a>
            <p class="mt-2">Реклама на Deels размещается по закону (ерид)</p>
        </div>
        <div class="container footer__items gap-3">
            <a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank">
                <img src="/images/promo/android.png" class="app_image" style="width: 160px">
            </a>
            <a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank">
                <img src="/images/promo/appstore.png" class="app_image" style="width: 160px">
            </a>
        </div>
    </footer>
    @endif
    </div>
    </div>

    <div class="popup__wrape">
        <div class="popup__modal">
            <div class="popup__close"><img src="/dist/images/icons/close.svg"></div>
            <div class="popup__title">Заголовок</div>
            <img class="popup__content" src="" alt="">
        </div>
    </div>
    @if(isset($show_twitch))
        @if(env('SHOW_STREAM'))
            <div id="stream_buttons_block" class="stream_buttons_block stream_buttons floating_buttons">
                <div id="stream_button" class="stream_button wave">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                         x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24"
                         enable-background="new 0 0 24 24" xml:space="preserve"><g id="Frames-24px">
                            <rect fill="none" width="24" height="24"/>
                        </g>
                        <g id="Solid">
                            <g>
                                <polygon fill="#ffffff" points="10,15 16,12 10,9 		"/>
                                <path fill="#ffffff"
                                      d="M21,2H3C1.897,2,1,2.896,1,4v16c0,1.103,0.897,2,2,2h18c1.103,0,2-0.897,2-2V4C23,2.896,22.103,2,21,2z			 M3,20V4h18l0.001,16H3z"/>
                                <rect x="5" y="5" fill="#ffffff" width="2" height="2"/>
                                <rect x="9" y="5" fill="#ffffff" width="2" height="2"/>
                                <rect x="13" y="5" fill="#ffffff" width="2" height="2"/>
                                <rect x="17" y="5" fill="#ffffff" width="2" height="2"/>
                                <rect x="5" y="17" fill="#ffffff" width="2" height="2"/>
                                <rect x="9" y="17" fill="#ffffff" width="2" height="2"/>
                                <rect x="13" y="17" fill="#ffffff" width="2" height="2"/>
                                <rect x="17" y="17" fill="#ffffff" width="2" height="2"/>
                            </g>
                        </g></svg>
                </div>

                @if(!\Cookie::get('twitch_block'))
                    <div class="stream_body">
                        <button type="button" id="stream_close" class="mfp-close stream_close">×</button>
                        <!-- Add a placeholder for the Twitch embed -->
                        <div id="twitch-embed"></div>
                        <div class="stream_block">
                            <form action="#" class="story-donate-form">
                                <div class="stream-donate-list">
                                    <button type="submit" class="btn btn_fill btn_flex donate_to_story"
                                            data-route="{{route('stream_donate')}}" data-story="">
                                        Задонатить
                                        <img src="/dist/img/deels_cur.svg" class="small_coin">
                                    </button>
                                    <input type="number" style="display: block" name="donate_amount"
                                           class="story-donate-input donate_amount" value=""
                                           placeholder="Укажите сумму">
                                </div>
                                <div class="stream_chat">
                                    <iframe
                                            id="chat_embed"
                                            src="https://www.twitch.tv/embed/{{env('TWITCH_CHANNEL')}}/chat?theme=dark&parent=deels.ru"
                                            height="300"
                                            width="100%">
                                    </iframe>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        @endif

    @endif
    <div id="main-div" class="floating_buttons">
        <div id="main-button" class="wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                <path d="M416 192c0-88.4-93.1-160-208-160S0 103.6 0 192c0 34.3 14.1 65.9 38 92-13.4 30.2-35.5 54.2-35.8 54.5-2.2 2.3-2.8 5.7-1.5 8.7S4.8 352 8 352c36.6 0 66.9-12.3 88.7-25 32.2 15.7 70.3 25 111.3 25 114.9 0 208-71.6 208-160zm122 220c23.9-26 38-57.7 38-92 0-66.9-53.5-124.2-129.3-148.1.9 6.6 1.3 13.3 1.3 20.1 0 105.9-107.7 192-240 192-10.8 0-21.3-.8-31.7-1.9C207.8 439.6 281.8 480 368 480c41 0 79.1-9.2 111.3-25 21.8 12.7 52.1 25 88.7 25 3.2 0 6.1-1.9 7.3-4.8 1.3-2.9.7-6.3-1.5-8.7-.3-.3-22.4-24.2-35.8-54.5z"
                      fill="#ffffff"/>
            </svg>
        </div>
        @php $footerTelegramUrl = trim((string) get_option('footer_telegram_url', true)); @endphp
        <a id="fba_tg" href="{{ !empty($footerTelegramUrl) ?  $footerTelegramUrl : 'https://t.me/+cGRkiSFIV6BiZTVi' }}" class="telegram-color" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.7 10.1l7.4-104.9 190.9-172.5c8.3-7.4-1.8-11.5-12.9-4.1L117.8 284 16.2 252.2c-22.1-6.9-22.5-22.1 4.6-32.7L418.2 66.4c18.4-6.9 34.5 4.1 28.5 32.2z"
                      fill="#ffffff"/>
            </svg>
        </a>
    </div>
    @stack('after_content')
    @yield('after_content')
    <script>
        window.DeelsFooterConfig = {!! json_encode([
        'isAuthenticated' => Auth::check(),
        'isAdmin' => Auth::check() && Auth::user()->is_admin(),
        'user' => Auth::check() ? [
            'id' => Auth::id(),
            'username' => Auth::user()->username,
            'avatar' => Auth::user()->avatar(),
        ] : null,
        'routes' => [
            'streamStatus' => route('stream.status'),
            'messagesShow' => route('messages.show'),
            'messagesGetList' => route('messages.get_list'),
            'messagesSendMessage' => route('messages.send_message'),
            'userAbuse' => route('user.abuse'),
            'login' => route('login'),
            'storeToken' => Auth::check() && isset($message_firebase) ? route('store.token') : null,
        ],
        'firebase' => Auth::check() && isset($message_firebase) ? [
            'enabled' => true,
            'scriptUrl' => 'https://www.gstatic.com/firebasejs/8.3.2/firebase.js',
            'config' => [
                'apiKey' => 'AIzaSyAlxpvXkADHwnhO-1082_JLTJC3GGuIn2Q',
                'authDomain' => 'kopiberi-299e9.firebaseapp.com',
                'projectId' => 'kopiberi-299e9',
                'storageBucket' => 'kopiberi-299e9.appspot.com',
                'messagingSenderId' => '333491145234',
                'appId' => '1:333491145234:web:5269438e8b4731e4f326cb',
                'measurementId' => 'G-P36GGP6Z05',
            ],
        ] : ['enabled' => false],
        'websocket' => [
            'url' => env('RATCHET_HOST', 'wss://deels.ru') . ':' . env('RATCHET_PORT', '443'),
            'userId' => auth()->id(),
        ],
        'twitch' => [
            'channel' => env('TWITCH_CHANNEL'),
            'scriptUrl' => 'https://player.twitch.tv/js/embed/v1.js',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="{{ext_asset('/dist/js/app.min.js')}}"></script>
    @if(session('clear_challenge_create_draft'))
        <script>
            try {
                localStorage.removeItem('challenge_create_draft_v2');
                sessionStorage.removeItem('challenge_create_draft_submitted_v2');
            } catch (e) {}
        </script>
    @endif

    @yield('page-js')

    @yield('additional_scripts')
    @stack('after_scripts')
    @yield('after_scripts')
    @include('dashboard.stories.stories_scripts')
    @include('dashboard.challenges.challenges_scripts')
    </body>
    </html>

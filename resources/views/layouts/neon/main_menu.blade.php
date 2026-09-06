<header class="deels-site-header">
    <div class="header">
        <div class="container">
            <a class="header__logo" href="{{route('home')}}" aria-label="DEELS — на главную">
                <img src="/dist/images/icons/deels.svg" alt="DEELS"/>
                <span class="header__brand-pill" aria-hidden="true">ARENA</span>
            </a>
            <div class="header__list desk">
                <ul>
                    @include('layouts.neon.partials.platform_links')
                </ul>
            </div>
            <div class="header__icons">
                <form action="/search" class="header__search ">
                    <button type="button" class="header__icon" id="searchOpen" aria-label="Открыть поиск">
                        <img src="/dist/images/icons/search.svg" alt="Поиск">
                    </button>
                    <input class="header__input hide" type="text" name="q" placeholder="Поиск...">
                </form>

                <button type="button" class="header__icon-hide" id="searchClose" aria-label="Закрыть поиск">
                    <img src="/dist/images/icons/close.svg" alt="Закрыть">
                </button>

                @if (Auth::check())
                    <div class="header-balance">
                        <div class="header-balance__btn">
                            <img src="/dist/images/icon-wallet.svg" alt="баланс">
                        </div>
                        <div class="header-balance__content">
                            <div class="text-center fw-600 fz-6">Баланс</div>
                            <div class="d-flex ai-center">
                                <span style="margin-right: 10px;">Мои дилсы:</span>
                                <span class="fw-600 fz-6">{{number_format(Auth::user()->wallet_balance, 0, ',', ',')}}</span>
                                <img src="/dist/img/deels_cur.svg" class="small_coin">
                            </div>
                            <div class="d-flex ai-center">
                                <span style="margin-right: 10px;">Мои средства:</span>
                                <span class="fw-600 fz-6">{{number_format(Auth::user()->profit_balance, 1, ',', ',')}}</span>
                                <span class="ruble-sign">₽</span>
                            </div>
                            <a href="{{ route('user_wallet') }}?deposit=true" class="btn btn_fill">Пополнить</a>
                        </div>
                    </div>
                <!-- Разметка чата -->
                <div class="header-chat">
                    <?php $count = \App\Models\Thread::forUserWithNewMessages(Auth::id())->latest('updated_at')->count(); ?>
                    <div class="header-chat__btn" {!! $count > 0 ? 'data-badge="'.$count.'"' : '' !!}>
                        <img src="/dist/images/icons/chat.svg" alt="Сообщения"/>
                    </div>


                    <div class="chat">
                        <div class="chat__wrap active" >
                            <!-- chat-head -->
                            <div class="chat-head">
                                <div class="chat-head__wrap">
                                    <div class="chat-head__title">Чаты</div>
                                    <button class="chat-head__btn" type="button" aria-label="close button" data-close>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g opacity="0.6"><path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g></svg>
                                    </button>
                                </div>
                                <div class="chat-head__wrap">
                                    <div class="chat-form">
                                        <div class="chat-form__wrap">
                                            <input type="text" name="chat_search" placeholder="Поиск по чатам" required>
                                            <button class="chat-form__btn" type="button" aria-label="search button">
                                                <svg width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_944_9641)"><path d="M9.55931 0C4.28845 0 0 4.28845 0 9.55931C0 14.8305 4.28845 19.1186 9.55931 19.1186C14.8305 19.1186 19.1186 14.8305 19.1186 9.55931C19.1186 4.28845 14.8305 0 9.55931 0ZM9.55931 17.3539C5.26145 17.3539 1.7648 13.8572 1.7648 9.55935C1.7648 5.26149 5.26145 1.7648 9.55931 1.7648C13.8572 1.7648 17.3538 5.26145 17.3538 9.55931C17.3538 13.8572 13.8572 17.3539 9.55931 17.3539Z" fill="white"/><path d="M21.4482 20.2007L16.3891 15.1416C16.0444 14.7969 15.4861 14.7969 15.1414 15.1416C14.7966 15.486 14.7966 16.0449 15.1414 16.3893L20.2004 21.4484C20.3728 21.6208 20.5984 21.707 20.8243 21.707C21.0499 21.707 21.2758 21.6208 21.4482 21.4484C21.7929 21.104 21.7929 20.5451 21.4482 20.2007Z" fill="white"/></g><defs><clipPath id="clip0_944_9641"><rect width="21.707" height="21.707" fill="white"/></clipPath></defs></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- chat-body -->
                            @include('messenger.list')
                        </div>

                        <div class="chat__wrap messages">

                        </div>
                        <div class="abuse_modal">
                            <form action="{{route('user.abuse')}}" class="abuse_form">
                                <input type="hidden" name="user_id" class="abuse_user_id" value=""/>
                                <input type="hidden" name="abuser_id" class="abuse_abuser_id" value="{{Auth::id()}}"/>
                                <input id="name" class="new__input abuse_reason" type="text" placeholder="Укажите причину жалобы" name="abuse" value="">
                                <div class="mt-4 d-flex">
                                    <button class="btn btn-small" type="submit">Отправить</button>
                                    <a class="btn btn-small btn-grey ml-2 abuse_close">Отмена</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Конец разметки чата -->

                <div class="header-chat">
                    @php
                        $systemThread = \Cmgmyr\Messenger\Models\Participant::select('thread_id')
                            ->whereIn('user_id', [Auth::id(), 0])
                            ->groupBy('thread_id')
                            ->havingRaw('COUNT(DISTINCT user_id) = 2')
                            ->first();
                    @endphp
                    <div class="header-chat__btn header-chat__btn--notifications"
                         {!! $count > 0 ? 'data-badge="'.$count.'"' : '' !!}
                         @if($systemThread) data-thread="{{ $systemThread->thread_id }}" @endif>
                        <img src="/dist/images/icons/notifications.svg" alt="Уведомления"/>
                    </div>
                </div>
                @endif

                <a href="{{ route('challenges.create') }}" class="deels-source-create">
                    <span aria-hidden="true">+</span> Создать
                </a>

                @if (Auth::check())
                    <a href="{{route('profile_edit')}}" class="header_profile">
                        <img src="/dist/images/icons/profile_fill.svg" alt="Профиль"/>
                    </a>
{{--                    <a href="{{route('messages')}}">💌</a>--}}
                @else
                    <a href="{{route('login')}}" class="header_profile">
                        <img src="/dist/images/icons/profile.svg" alt="Профиль"/>
                    </a>
                @endif
                <button type="button" id="menuOpen" aria-label="Открыть меню" aria-controls="deels-mobile-menu" aria-expanded="false">
                    <img src="/dist/images/icons/menu.svg" alt="Меню"/>
                </button>
            </div>
        </div>
        <div class="header__menu" id="deels-mobile-menu">
            <button type="button" id="menuClose" aria-label="Закрыть меню">
                <img src="/dist/images/icons/close.svg" alt="Закрыть"/>
            </button>
            <ul class="header__menu-list" style="width: 100%">
                <li>
                    <form action="/search" class="header__search header__search-show" style="display: block; width: 100%; padding-bottom: 10px; border-bottom: 1px solid white; position: relative">
                        <input class="header__input header__input-show" type="text" name="q" placeholder="Поиск..."  style="display: block; width: 100%; padding-left: 0px; margin-left: 0px">
                        <button type="submit" class="header__icon" style="background: transparent; border: 0; position: absolute; top: 0; right: 0;margin-right: 0">
                            <img src="/dist/images/icons/search.svg" alt="Поиск">
                        </button>
                    </form>
                </li>
                @include('layouts.neon.partials.platform_links')

                <li class="header__menu-divider" aria-hidden="true"></li>
                <li @if(request()->routeIs('home')) class="active" @endif>
                    <a href="{{ route('home') }}">Главная</a>
                </li>
                @foreach($header_menu_pages as $page)
                    <li @if(request()->routeIs('single_page') && request()->route('slug') === $page->slug) class="active" @endif>
                        <a href="{{ route('single_page', $page->slug) }}">{{ $page->title }}</a>
                    </li>
                @endforeach
                <li @if(request()->routeIs('start_campaign')) class="active" @endif>
                    <a href="{{ route('start_campaign') }}">Создать копилку</a>
                </li>
                <li @if(\Illuminate\Support\Facades\Route::currentRouteName()==='contact_us') class="active" @endif>
                    <a href="{{route('contact_us')}}">@lang('app.contact_us')</a>
                </li>
            </ul>

{{--            <ul class="header__menu-list">--}}
{{--                @foreach($categories as $category)--}}
{{--                    <li>--}}
{{--                        <a href="/campaigns?category={{$category->id}}">{{$category->category_name}}</a>--}}
{{--                    </li>--}}
{{--                @endforeach--}}
{{--                <li>--}}
{{--                    <a href="/campaigns?type=fully_donated">Накопленные на 100%</a>--}}
{{--                </li>--}}
{{--            </ul>--}}
        </div>
    </div>

    <style>
        #mobileshow {
            display: none;
        }

        #mobileshow.fixed {
            background: #0d102c;
        }

        @media screen and (max-width: 500px) {
            #mobileshow {
                display: block;
            }
        }
    </style>
</header>

<div class="background">
    <div class="background__filter">

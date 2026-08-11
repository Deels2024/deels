<header class="header cd-auto-hide-header bg-gray">
    <div class="wrapper header__wrap flex">
        <div class="header__first flex">
            <div class="header__burger">
                <span></span>
                <span></span>
            </div>
            <a href="{{route('home')}}" class="header__logo img-contain">
                @if(get_option('logo_settings') === 'show_site_name')
                    {{ get_option('site_name') }}
                @else
                    @if(logo_url())
                        <img class="lozad" data-src="{{ logo_url() }}" alt="">
                    @else
                        {{ get_option('site_name') }}
                    @endif
                @endif
            </a>
        </div>
        <nav class="header__menu">
            <ul>
                <li @if(request()->route()->getName()==='home') class="active" @endif>
                    <a href="{{route('home')}}">Главная</a>
                </li>
                @php
                    $header_menu_pages = \App\Models\Post::whereStatus(1)->where('show_in_header_menu', 1)->get();
                @endphp
                @if($header_menu_pages->count() > 0)
                    @foreach($header_menu_pages as $page)
                        <li @if(request()->route()->getName()==='single_page') class="active" @endif>
                            <a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a>
                        </li>
                    @endforeach
                @endif
                <li @if(request()->route()->getName()==='browse_campaigns') class="active" @endif>
                    <a href="{{route('browse_campaigns')}}">Копилки</a>
                </li>
                <li @if(request()->route()->getName()==='start_campaign') class="active" @endif>
                    <a href="{{route('start_campaign')}}" style="font-weight: 800">@lang('app.start_campaign')</a>
                </li>
                <li @if(request()->route()->getName()==='contact_us') class="active" @endif>
                    <a href="{{route('contact_us')}}">@lang('app.contact_us')</a>
                </li>
                <li @if(request()->route()->getName()==='card_pay') class="active" @endif>
                    <a href="{{route('offer')}}">Правила и оферты</a>
                </li>
            </ul>
        </nav>
        <div class="header__last flex">
            <form action="{{route('search')}}" class="header__search">
                <div class="header__search-btn img-contain">
                    <img src="/images/icons/search.svg" alt="">
                </div>
                <div class="header__search-box">
                    <input type="text" name="q" class="header__search-field field" placeholder="Поиск" required>
                    <button class="header__search-button flex-center img-contain">
                        <img src="/images/icons/search.svg" alt="">
                    </button>
                </div>
            </form>

            @if (Auth::guest())
                <div class="header__man img-contain">
                    <a href="{{route('login')}}"><img src="/images/icons/man.svg" alt=""></a>
                </div>
            @else
                <a href="{{route('dashboard')}}" class="header__profile flex">
                    <div class="header__profile-avatar img-cover">
                        <img src="{{Auth::user()->avatar() ?? '/images/icons/man.svg'}}" alt="" class="magnific_image circle-img">
                    </div>
                    <div class="header__profile-name">{{Auth::user()->fullname}}</div>
                </a>
            @endif

        </div>
    </div>
</header>

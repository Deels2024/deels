<footer class="footer">
    <div class="wrapper footer__wrap flex">
        <a href="{{route('home')}}" class="footer__logo">
            @if(get_option('logo_settings') == 'show_site_name')
                {{ get_option('site_name') }}
            @else
                @if(logo_url())
                    <img src="{{ logo_url() }}" alt="">
                @else
                    {{ get_option('site_name') }}
                @endif
            @endif
        </a>
        <div class="footer__items flex">
            <div class="footer__item flex">
                <div class="footer__icon img-contain">
                    <img src="/images/icons/location.svg" alt="">
                </div>
                <div class="footer__desc">Санкт-Петербург, пр. Ветеранов 166, лит. А</div>
            </div>
            <a href="tel:+7 (812) 5079808" class="footer__item flex">
                <div class="footer__icon img-contain">
                    <img src="/images/icons/phone.svg" alt="">
                </div>
                <div class="footer__desc">+7 (812) 5079808</div>
            </a>
            <a href="mailto:hello@helpus.com" class="footer__item flex">
                <div class="footer__icon img-contain">
                    <img src="/images/icons/mail.svg" alt="">
                </div>
                <div class="footer__desc">info@deels.ru</div>
            </a>
        </div>
        <a href="/docs/%D0%9F%D0%BE%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B5%20%D0%BE%20%D0%BA%D0%BE%D0%BD%D1%84%D0%B8%D0%B4%D0%B5%D0%BD%D1%86%D0%B8%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D1%81%D1%82%D0%B8%20dsa.docx"
           class="footer__confidentiality">Политика конфиденциальности</a>
    </div>
</footer>
<div class="bottom">
    <div class="wrapper bottom__wrap">
        <div class="bottom__closed img-contain">
            <button class="btn" style="background-color: #E1AB3C;border-color: #E1AB3C;color:#fff;padding: 10px 23px;">
                Принять
            </button>
        </div>
        <div class="bottom__text">Этот веб-сайт использует файлы cookie для повышения удобства работы. Переход по ссылке
            на наш
            веб-сайт или работа на веб-сайте подразумевает согласие со сбором данных посредством файлов coockie.
            {{--            <button class="btn" style="background-color: #E1AB3C;border-color: #E1AB3C;color:#fff;padding: 10px 23px;">Принять</button>--}}
        </div>
    </div>
</div>
</div>

<div class="menu">
    <div class="menu__wrap">
        <div class="menu__top flex">
            <div class="menu__closed img-contain">
                <img src="/images/icons/close.svg" alt="">
            </div>
            <div class="menu__links">
                <ul>
                    <li>
                        <a href="#">Facebook</a>
                    </li>
                    <li>
                        <a href="#">Instagram</a>
                    </li>
                    <li>
                        <a href="#">Telegram</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="menu__list menu__list_mobile">
            <ul>
                <li @if(request()->route()->getName()==='home') class="active" @endif>
                    <a href="{{route('home')}}">Главная</a>
                </li>
                @php
                    $header_menu_pages = \App\Models\Post::whereStatus(1)->where('show_in_header_menu', 1)->get();
                @endphp
                @if($header_menu_pages->count() > 0)
                    @foreach($header_menu_pages as $page)
                        <li>
                            <a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a>
                        </li>
                    @endforeach
                @endif
                <li @if(request()->route()->getName()==='browse_campaigns') class="active" @endif>
                    <a href="{{route('browse_campaigns')}}">Копилки</a>
                </li>
                <li @if(request()->route()->getName()==='start_campaign') class="active" @endif>
                    <a href="{{route('start_campaign')}}">@lang('app.start_campaign')</a>
                </li>
                <li @if(request()->route()->getName()==='contact_us') class="active" @endif>
                    <a href="{{route('contact_us')}}">@lang('app.contact_us')</a>
                </li>
            </ul>
        </div>
        <div class="menu__list">
            <ul>
                @foreach(\App\Models\Category::all() as $category)
                    <li>
                        <a href="/campaigns?category={{$category->id}}">{{$category->category_name}}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>


<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/libs/mask/jquery.maskedinput.min.js"></script>
<script src="/js/libs/fancybox/jquery.fancybox.min.js"></script>
<script src="/js/libs/swiper/swiper.min.js"></script>
<script src="/js/libs/nouislider/nouislider.min.js"></script>
<script src="/js/libs/nouislider/wNumb.min.js"></script>
@yield('page-js')
<script src="/js/common.js"></script>
@yield('additional_scripts')

@isset($newsItem)
    <script>
        {{--setCookie('lastNews', {{$newsItem->id}}, 999)--}}
        {{--Swal.fire({--}}
        {{--	title: '<b style="color: #000">{{$newsItem->title}}</b>',--}}
        {{--	// icon: 'info',--}}
        {{--	html:'{!! html_entity_decode($newsItem->text) !!}',--}}
        {{--	confirmButtonText:'Понятно!',--}}
        {{--    customClass: {--}}
        {{--		confirmButton: 'btn'--}}
        {{--    }--}}
        {{--})--}}
    </script>

@endif

<script>
    window.lozad().observe();
</script>


</body>

</html>

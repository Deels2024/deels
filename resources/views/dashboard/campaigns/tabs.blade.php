<div class="d-block mb-4 mt-7">
    <ul class="main-content__switch lk_switch">
        <li class="main-content__switch-link main-content__switch-link_donate {{\Route::currentRouteName() == 'dashboard' ? 'main-content__switch-link_active' : ''}}">
            <a class="main-content__switch-link" href="{{ route('dashboard') }}">Активные копилки</a>
        </li>
        <li class="main-content__switch-link main-content__switch-link_comments {{\Route::currentRouteName() == 'my_pending_campaigns' ? 'main-content__switch-link_active' : ''}}">
            <a class="main-content__switch-link" href="{{route('my_pending_campaigns')}}">Неактивные копилки</a>
        </li>
{{--        <li class="main-content__switch-link main-content__switch-link_comments {{\Route::currentRouteName() == 'backed_campaigns' ? 'main-content__switch-link_active' : ''}}">--}}
{{--            <a class="main-content__switch-link" href="{{route('backed_campaigns')}}">Поддержанные копилки</a>--}}
{{--        </li>--}}
    </ul>
</div>
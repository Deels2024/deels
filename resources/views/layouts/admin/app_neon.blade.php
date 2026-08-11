@include('layouts.neon.header')
@include('inc.alerts')
<div class="account">
    <div class="container account__container">
        <div class="account__sidebar sidebar">
            <div class="sidebar__close mobile">
                <img src="/dist/images/icons/close.svg"/>
            </div>
            <h2>
                Личный кабинет
                <span>Личный кабинет</span>
            </h2>
            <div class="sidebar__list">
                @if(Auth::user() && Auth::user()->is_admin())
                    @include('layouts.admin.parts.admin_menu')
                @endif
                @include('layouts.admin.parts.menu')

            </div>
        </div>
        <div class="sidebar__open sidebar__open-pos mobile">
            Открыть меню
        </div>
        <div class="account__content">
            @yield('content')
        </div>
    </div>
</div>
@include('auth.select_email_modal')
@include('auth.select_phone_modal')
@include('auth.suspicious_activity_modal')
@include('layouts.neon.footer')

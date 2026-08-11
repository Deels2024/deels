@php
    $auth_user = \Illuminate\Support\Facades\Auth::user();
@endphp
<ul>
    @if(!$auth_user->is_comment_admin() && !$auth_user->is_campaign_admin())
        <li class="active">
            <a href="{{ route('dashboard') }}">Мои копилки</a>
        </li>
        <li class="menu-item-has-children">
            <a href="#">Копилки</a>
            <ul>
                {{--                <li class="children-active"><a href="{{route('my_campaigns')}}">@lang('app.my_campaigns')</a></li>--}}
                <li><a href="{{route('start_campaign')}}">@lang('app.start_a_campaign')</a></li>
                <li><a href="{{route('my_pending_campaigns')}}">@lang('app.pending_campaigns')</a></li>
            </ul>
        </li>
    @endif
    @if(!$auth_user->is_campaign_admin())
        <li style="position: relative">
            <a href="{{ route('admin_comments') }}"> @lang('app.comments') </a>
            @if($commentsCount)
                <span style="display: block;
                                    position: absolute;
                                    bottom: 9px;
                                    right: 75px;
                                    background: #e41616;
                                    border-radius: 50px;
                                    width: 25px;
                                    height: 25px;
                                    color: #fff;
                                    text-align: center;">{{$commentsCount}}</span>
            @endif
        </li>
    @elseif($auth_user->is_campaign_admin())
        <li>
            <a href="{{ route('campaigns_to_moderate') }}">
                @lang('app.moderate_campaigns')
            </a>
        </li>
    @endif
    @if($auth_user->is_admin())
        <li><a href="{{ route('categories') }}">@lang('app.categories')</a></li>
        <li class="menu-item-has-children">
            <a href="#"> @lang('app.campaigns')</a>
            <ul>
                <li class="children-active"><a href="{{ route('all_campaigns') }}">@lang('app.all_campaigns')</a></li>
                <li><a href="{{ route('staff_picks') }}">@lang('app.staff_picks')</a></li>
                <li><a href="{{ route('funded') }}">@lang('app.full_funded')</a></li>
                <li><a href="{{ route('blocked_campaigns') }}">@lang('app.blocked_campaigns')</a></li>
                <li><a href="{{ route('pending_campaigns') }}">@lang('app.pending_campaigns')</a></li>
                <li><a href="{{ route('expired_campaigns') }}">@lang('app.expired_campaigns')</a></li>
            </ul>
        </li>

        <li class="menu-item-has-children">
            <a href="#">@lang('app.settings')</a>
            <ul>
                <li class="children-active"><a href="{{ route('general_settings') }}">@lang('app.general_settings')</a></li>
                <li><a href="{{ route('payment_settings') }}">@lang('app.payment_settings')</a></li>
                <li><a href="{{ route('theme_settings') }}">@lang('app.theme_settings')</a></li>
                <li><a href="{{ route('social_settings') }}">@lang('app.social_settings')</a></li>
                <li><a href="{{ route('re_captcha_settings') }}">@lang('app.re_captcha_settings')</a></li>
            </ul>
        </li>
        <li>
            <a href="{{ route('pages') }}">@lang('app.pages')</a>
        </li>
        <li>
            <a href="{{route('users')}}">@lang('app.users')</a>
        </li>
        <li>
            <a href="{{route('withdrawal_requests')}}"> @lang('app.withdrawal_requests')</a>
        </li>
        <li>
            <a href="{{route('requested_account_deletion')}}">@lang('app.requested_account_deletion')</a>
        </li>
            <li>
                <a href="{{route('logs')}}"> @lang('app.logs')</a>
            </li>
        <li class="menu-item-has-children">
            <a href="#">@lang('app.news')</a>
            <ul>
                <li class="children-active">
                    <a href="{{route('news_list')}}">Список новостей</a>
                </li>
                <li>
                    <a href="{{route('news_create_page')}}">Создать новость</a>
                </li>
            </ul>
        </li>
    @endif
    <li>
        <a href="{{route('autopayments')}}">Автоплатежи</a>
    </li>
    @if(!$auth_user->is_comment_admin() && !$auth_user->is_campaign_admin())
        <li><a href="{{route('payments')}}">@lang('app.payments')</a>
            @if($paymentsCount && Auth::user()->is_admin())
                <span style="display: block;
                                    position: absolute;
                                    bottom: 9px;
                                    right: 75px;
                                    background: #e41616;
                                    border-radius: 50px;
                                    width: 25px;
                                    height: 25px;
                                    color: #fff;
                                    text-align: center;">{{$paymentsCount}}</span>
            @endif
        </li>
        <li><a href="{{route('backed_campaigns')}}">@lang('app.backed_campaigns')</a></li>
        <li><a href="{{route('withdraw')}}">@lang('app.withdraw')</a></li>
        <li><a href="{{route('profile_edit')}}">@lang('app.profile')</a></li>
        <li><a href="{{route('change_password')}}">@lang('app.change_password')</a></li>
    @endif
    <li>
        <a href="{{route('logout')}}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">Выйти</a>
        <form id="logout-form" action="{{ url('/logout') }}"
              method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </li>
</ul>

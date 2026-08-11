<ul>

    @if(Auth::user())
        <a href="{{ route('dashboard') }}">Копилки</a>


        <li class="banks__open">
            <a href="#">Сторис<img src="/dist/images/icons/arrow_down.svg" alt=""/></a>
            <div class="sidebar__hide" style="position: relative; top: -20px;">
                <ul>
                    <li><a href="{{ route('user_stories') }}">Мои сторис</a></li>
                    <li><a href="{{ route('user_likes') }}">Мои лайки </a></li>
                </ul>
            </div>
        </li>

        <li class="banks__open">
            <a href="#">Челленджи<img src="/dist/images/icons/arrow_down.svg" alt=""/></a>
            <div class="sidebar__hide" style="position: relative; top: -20px;">
                <ul>
                    <li>
                        <a href="{{route('user_challenges')}}">Мои челленджи</a>
                    </li>
                    <li>
                        <a href="{{route('user_challenges')}}?type=participant">Я участвую</a>
                    </li>
                </ul>
            </div>
        </li>


        <li class="banks__open">
            <a href="#">Финансы<img src="/dist/images/icons/arrow_down.svg" alt=""/></a>
            <div class="sidebar__hide" style="position: relative; top: -20px;">
                <ul>
                    <li> <a href="{{ route('user_wallet') }}">Мой кошелек</a></li>
                    <li> <a href="{{route('autopayments')}}">Автоплатежи</a></li>
                </ul>
            </div>
        </li>

        <li class="banks__open">
            <a href="#">Профиль<img src="/dist/images/icons/arrow_down.svg" alt=""/></a>
            <div class="sidebar__hide" style="position: relative; top: -20px;">
                <ul>
                    <li><a href="{{route('profile_edit')}}">Статистика профиля</a></li>
                    <li>
                        <a href="{{ route('user_friends') }}">Друзья</a>
                    </li>
                    <li><a href="{{route('profile_settings')}}">Настройки</a></li>
                    <li><a href="{{route('change_password')}}">@lang('app.change_password')</a></li>
                </ul>
            </div>
        </li>
    @endif


    @if(Auth::user() && !Auth::user()->is_comment_admin() && !Auth::user()->is_campaign_admin())
        @php
            //$campaign_ids = auth()->user()->my_campaigns()->pluck('id')->toArray();
           // $payments = \App\Models\Payment::query()->whereIn('campaign_id', $campaign_ids)->where('status', 'pending')->count();
        @endphp





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

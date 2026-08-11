<style>
    .admin_menu {
        position: relative;
        padding: 25px 15px 15px 15px;
        margin: 0 -15px 20px -15px;
        border-radius: 5px;
        background: rgba(0, 0, 0, 0.21);
    }
    .admin_menu:before {
        position: absolute;
        content: 'Администратор';
        text-transform: uppercase;
        font-size: 9px;
        font-weight: bold;
        top: -5px;
        left: 0;
        right: 0;
        text-align: center;
        padding: 5px 10px;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        background: #23133c;
    }
    .admin_menu a{
        white-space: normal;
        margin-bottom: 15px;
    }
    .admin_menu li:last-child a{
        margin-bottom: 0;
    }
</style>
<ul class="admin_menu">
    @if(Auth::user() && Auth::user()->is_admin())
        <li>
            <a href="{{ route('admin_stories') }}"> Сторис </a>
        </li>

        <li>
            <a href="{{ route('admin_stories_ads') }}"> Реклама </a>
        </li>
        <li>
            <a href="{{ route('admin_challenges') }}"> Челленджи </a>
        </li>
        <li>
            <a href="{{route('admin_battles')}}"> Батлы</a>
        </li>

        <li>
            <a href="{{ route('admin_challenges_stories') }}"> Ответы на челленджи </a>
        </li>

        <li>
            <a href="{{ route('admin_battles_stories') }}"> Ответы на батлы </a>
        </li>

        <li>
            <a href="{{ route('admin_games') }}">🕹 Настройки игр </a>
        </li>
        <li>
            <a href="{{ route('admin_game_sessions') }}">🕹 Игровые сессии </a>
        </li>



        <li>
            <a href="{{ route('admin_comments') }}"> @lang('app.comments') </a>
        </li>

{{--        <li>--}}
{{--            <a href="{{ route('campaigns_to_moderate') }}">--}}
{{--                @lang('app.moderate_campaigns')--}}
{{--            </a>--}}
{{--        </li>--}}
    @endif
    @if(Auth::user() && Auth::user()->is_admin())
        <li class="banks__open">
            <a href="#"> Копилки к модерации<img src="/dist/images/icons/arrow_down.svg" alt=""/></a>
            <div class="sidebar__hide" style="position: relative; top: -20px;">
                <ul>
                    <li>
                        <a href="{{ route('all_campaigns') }}">@lang('app.all_campaigns')</a>
                    </li>
{{--                    <li><a href="{{ route('staff_picks') }}">@lang('app.staff_picks')</a></li>--}}
                    <li><a href="{{ route('funded') }}">@lang('app.full_funded')</a></li>
                    <li><a href="{{ route('blocked_campaigns') }}">@lang('app.blocked_campaigns')</a>
                    </li>
                    <li><a href="{{ route('pending_campaigns') }}">@lang('app.pending_campaigns')</a>
                    </li>
                    <li><a href="{{ route('expired_campaigns') }}">@lang('app.expired_campaigns')</a>
                    </li>
                </ul>
            </div>
        </li>

            @php
                $campaign_ids = auth()->user()->my_campaigns()->pluck('id')->toArray();
                $payments = \App\Models\Payment::query()->whereIn('campaign_id', $campaign_ids)->where('status', 'pending')->count();
            @endphp

        <li>
            <a href="{{route('transactions')}}"> Транзакции</a>
        </li>
        <li>
            <a href="/dashboard/admin_stories/likes">👍️️️️️ ️История лайков</a>
        </li>
        <li>
            <a href="/dashboard/admin_stories/dislikes">👎️️ История дизлайков</a>
        </li>

        <li>
            <a href="{{route('stats')}}"> Статистика</a>
        </li>


            <li><a href="{{route('payments')}}">@lang('app.payments')
                    @if(Auth::user()->is_admin())
                        <sup @if($payments>0) style="color: red;font-weight: bold;" @endif>{{$payments}}</sup>
                    @endif
                </a>
            </li>
        <li>
            <a href="{{route('users')}}">@lang('app.users')</a>
        </li>
        <li>
            <a href="{{route('mailing')}}"> Рассылки</a>
        </li>
        <li>
            <a href="{{route('abuses_list')}}"> Жалобы</a>
        </li>
        <li>
            <a href="{{route('thank_list')}}"> Благодарности</a>
        </li>
        <li>
            <a href="{{route('withdrawal_requests')}}"> @lang('app.withdrawal_requests')</a>
        </li>
        <li>
            <a href="{{route('requested_account_deletion')}}">@lang('app.requested_account_deletion')</a>
        </li>
        <li>
            <a href="{{ route('admin_tags') }}"> Теги </a>
        </li>
        <li>
            <a href="{{ route('logs') }}"> Логи </a>
        </li>

        <li>
            <a href="{{route('general_settings')}}">Настройки</a>
        </li>



    @endif

</ul>
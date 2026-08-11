<a href="{{$route}}" class="challenge-card">
    <div class="challenge-card__head">
        <img src="{{$battle->user->avatar()}}" alt="{{$battle->user->username}}" height="40" width="40">
        <span>{{$battle->user->username}}</span>
    </div>
    @if($battle->type == 'video')
        @if($battle->video_preview)
            <video src="{{$battle->video_preview}}" poster="{{$battle->thumbnail}}" muted loop autoplay playsinline class="copystories-item__img"></video>
        @else
            <img src="{{$battle->thumbnail}}" alt="Батл #{{$battle->title}}">
        @endif
    @else
        <img src="{{$battle->thumbnail ?: $battle->path}}" alt="Батл #{{$battle->title}}">
    @endif
    <div class="challenge-card__content">
        <h3>{{$battle->title}}</h3>
        @if(!$battle->finished && $battle->daysLeft())
        <p class="challenge-card__date">
            @if(!$battle->started && $battle->min_participants)
                Идет набор
            @else
                {{trans_choice('numbers.left', $battle->end_days)}} {{$battle->daysLeft()}}
                @if(isset($moderation))
                    <br>{{\Carbon\Carbon::parse($battle->finish)->format('d.m.Y H:i')}}
                @endif
            @endif
        </p>
        @elseif($battle->finished)
            <p class="challenge-card__date">Челлендж завершен</p>
        @else
        @endif
        @if(isset($dashboard) && $dashboard)
            @if($battle->declined)
                <div class="copystories-item__info mt-2">[ Отклонен ]</div>
            @else
                {!! !$battle->active ? '<div class="copystories-item__info mt-2">[ На модерации ]</div>' : '' !!}
            @endif
        @endif
        @if(isset($show_ai_moderation))
            @if(isset($_GET['type']) && $_GET['type'] == 'declined')
                @if($battle->moderation)
                <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                    {!! $battle->getReasons()!!}
                </div>
                @endif
            @endif
        @endif
        <?php
            $battle_winners_count = count($battle->winners);
        ?>
        @if($battle_winners_count)
            @if($battle_winners_count == 1)
{{--                <p>Победитель:</p>--}}
{{--                @foreach($battle->winners as $winner)--}}
{{--                    <div class="d-flex ai-center gap-3 mt-2 mb-5">--}}
{{--                        <img src="{{$winner->avatar() ?? ''}}" alt="" height="28" width="28">--}}
{{--                        <span>{{$winner->fullname ?? ''}}</span>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
            @else
{{--                <p>Победителей: {{$battle_winners_count}}</p>--}}
            @endif
{{--            <b class="text-accent">Смотреть</b>--}}
        @endif
{{--        <div class="copystories-item__info mt-7">--}}
{{--            <div class="copystories-info copystories-info--views">{{$battle->views_count}}</div>--}}
{{--            <div class="copystories-info copystories-info--likes">{{$battle->likes_count}}</div>--}}
{{--            <div class="copystories-info copystories-info--comments">{{$battle->comments_count}}</div>--}}
{{--        </div>--}}
                <div class="copystories-item__info mt-3">
                    @php
                        $participants = $battle->stories()->active()->count();
                    @endphp
            @if(!$battle->finished)
{{--            <div class="btn_pill copystories-info copystories-info--participants">--}}

{{--                @if($battle->cost && $battle->cost > 0)--}}
{{--                    Участие: {{number_format($battle->cost, 0)}} <img class="coin" src="/dist/images/deels_coin_small.png" srcset="/dist/images/deels_coin_small.png" alt="DEELS" style="width: 12px; height: auto; margin-left: 2px;">--}}
{{--                @else--}}
{{--                    Участие бесплатно!--}}
{{--                @endif--}}

{{--                @if($participants == 0)--}}
{{--                    Прими участие!--}}
{{--                @else--}}
{{--                    {{$participants}} {{trans_choice('numbers.participants', $participants)}}--}}
{{--                @endif--}}
{{--            </div>--}}

            @endif
        </div>

        @if(!$battle->finished)
        <div class="btn_fill comment_action" data-action="finish"
             data-id="{{$battle->id}}" href="javascript:;"
             style="cursor:pointer;">[ Завершить ]</div>
        @endif

        @if(isset($moderation))
            <div class="d-flex ai-center">
                <div class="actions">
                    <div class="actions-link challenge_delete" data-action="delete"
                         data-id="{{$battle->id}}"
                         style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></div>

                </div>
                <p class="comment-id comment-id--body mr-1">#{{$battle->id}}</p>
            </div>
        @endif
    </div>
</a>

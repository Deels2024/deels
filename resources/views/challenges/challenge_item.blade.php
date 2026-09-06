<a href="{{$route}}" class="challenge-card">
    <div class="challenge-card__head">
        <img src="{{$challenge->user->avatar()}}" alt="{{$challenge->user->username}}" height="40" width="40">
        <span>{{$challenge->user->username}}</span>
    </div>
    @if($challenge->type == 'video')
        @if($challenge->video_preview)
            <video src="{{$challenge->video_preview}}" poster="{{$challenge->thumbnail}}" muted loop autoplay playsinline class="copystories-item__img"></video>
        @else
            <img src="{{$challenge->thumbnail}}" alt="{{!empty($isBattle) ? 'Батл' : 'Челлендж'}} #{{$challenge->title}}">
        @endif
    @else
        <img src="{{$challenge->thumbnail ?: $challenge->path}}" alt="{{!empty($isBattle) ? 'Батл' : 'Челлендж'}} #{{$challenge->title}}">
    @endif
    <div class="challenge-card__content">
        <h3>{{$challenge->title}}</h3>
        @if(!$challenge->finished && $challenge->daysLeft())
        <p class="challenge-card__date">
            @if(!$challenge->started && $challenge->min_participants)
                Идет набор
            @else
                {{trans_choice('numbers.left', $challenge->end_days)}} {{$challenge->daysLeft()}}
                @if(isset($moderation))
                    <br>{{\Carbon\Carbon::parse($challenge->finish)->format('d.m.Y H:i')}}
                @endif
            @endif
        </p>
        @elseif($challenge->finished)
            <p class="challenge-card__date">{{!empty($isBattle) ? 'Батл' : 'Челлендж'}} завершен</p>
        @else
        @endif
        @if(isset($dashboard) && $dashboard)
            @if($challenge->declined)
                <div class="copystories-item__info mt-2">[ Отклонен ]</div>
            @else
                {!! !$challenge->active ? '<div class="copystories-item__info mt-2">[ На модерации ]</div>' : '' !!}
            @endif
        @endif
        @if(isset($show_ai_moderation))
            @if(isset($_GET['type']) && $_GET['type'] == 'declined')
                @if($challenge->moderation)
                <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                    {!! $challenge->getReasons()!!}
                </div>
                @endif
            @endif
        @endif
        <?php
            $challenge_winners_count = $challenge->relationLoaded('winners')
                ? $challenge->winners->count()
                : $challenge->winners()->count();
        ?>
        @if($challenge_winners_count)
            @if($challenge_winners_count == 1)
{{--                <p>Победитель:</p>--}}
{{--                @foreach($challenge->winners as $winner)--}}
{{--                    <div class="d-flex ai-center gap-3 mt-2 mb-5">--}}
{{--                        <img src="{{$winner->avatar() ?? ''}}" alt="" height="28" width="28">--}}
{{--                        <span>{{$winner->fullname ?? ''}}</span>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
            @else
{{--                <p>Победителей: {{$challenge_winners_count}}</p>--}}
            @endif
{{--            <b class="text-accent">Смотреть</b>--}}
        @endif
{{--        <div class="copystories-item__info mt-7">--}}
{{--            <div class="copystories-info copystories-info--views">{{$challenge->views_count}}</div>--}}
{{--            <div class="copystories-info copystories-info--likes">{{$challenge->likes_count}}</div>--}}
{{--            <div class="copystories-info copystories-info--comments">{{$challenge->comments_count}}</div>--}}
{{--        </div>--}}
                <div class="copystories-item__info mt-3">
                    @php
                        $participants = array_key_exists('stories_count', $challenge->getAttributes())
                            ? (int) $challenge->getAttributes()['stories_count']
                            : $challenge->stories()->active()->count();
                    @endphp
            @if(!$challenge->finished)
{{--            <div class="btn_pill copystories-info copystories-info--participants">--}}

{{--                @if($challenge->cost && $challenge->cost > 0)--}}
{{--                    Участие: {{number_format($challenge->cost, 0)}} <img class="coin" src="/dist/images/deels_coin_small.png" srcset="/dist/images/deels_coin_small.png" alt="DEELS" style="width: 12px; height: auto; margin-left: 2px;">--}}
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

        @if(isset($moderation))
            <div class="d-flex ai-center">
                <div class="actions">
                    <div class="actions-link show_challenge" data-route="{{route('challenge_page', $challenge->id)}}"
                         data-id="{{$challenge->id}}" href="javascript:;"
                         style="background-image: url(/dist/images/admin_icons/icon-eye.svg)"></div>
                    @if(!$challenge->declined)
                        <div class="actions-link comment_action" data-action="trash"
                           data-id="{{$challenge->id}}" href="javascript:;"
                           style="background-image: url(/dist/images/admin_icons/icon-cancel.svg); background-size: 9px"></div>
                    @else
                        <div class="actions-link challenge_delete" data-action="delete"
                             data-id="{{$challenge->id}}"
                             style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></div>
                    @endif
                    @if($challenge->finished)
                        <div class="actions-link comment_action" data-action="restart"
                             data-id="{{$challenge->id}}" href="javascript:;"
                             style="background-image: url(/dist/images/admin_icons/icon-restart.svg); background-size: 9px" title="Перезапустить"></div>
                    @endif


                    @if (!$challenge->active)
                        <div class="actions-link comment_action" data-action="approve"
                           data-id="{{$challenge->id}}" href="javascript:;"
                           style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></div>
                    @endif
                    <div class="actions-link show_challenge" data-route="{{ route('challenges.edit', [$challenge->id]) }}"
                         data-id="{{$challenge->id}}" href="javascript:;"
                         style="background-image: url(/dist/images/admin_icons/icon-edit.svg)"></div>
                </div>
                <p class="comment-id comment-id--body mr-1">#{{$challenge->id}}</p>
            </div>
        @endif
    </div>
</a>

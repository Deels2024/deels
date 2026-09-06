        <div class="contest-overview">
            <aside class="contest-overview__aside">

                <div class="challenge-info__media">
                    @if($mainStory)
                        <a href="#story-popup" class="challenge-media challenge-media--play show_story"
                           data-route="{{route('stories.preview', ['id' => $mainStory->id, 'user_id' => $ownerId])}}"
                           data-story="{{$mainStory->id}}" data-type="{{$mainStory->type}}"
                           data-paid="{{$mainStory->paid}}" data-amount="{{$mainStory->amount}}">
                            @include('challenges.partials.contest_media', ['contest' => $contest])
                            @include('challenges.partials.contest_finished_badge', ['contest' => $contest, 'contestTitle' => $contestTitle])
                        </a>
                    @else
                        <div class="challenge-media">
                            @include('challenges.partials.contest_media', ['contest' => $contest])
                            @include('challenges.partials.contest_finished_badge', ['contest' => $contest, 'contestTitle' => $contestTitle])
                        </div>
                    @endif

                        @if($isOwner)
                            <div class="challenge-media-actions">
                                <a href="{{route('stories.create', [$routeParam => $contest->id, 'useful' => 1])}}"
                                   class="challenge-btn challenge-btn--fill" data-toggle="tooltip" data-tooltip="Добавить полезное">
                                    <i class="challenge-btn__icon" style="background-image: url('/img/useful.svg')"></i>
                                </a>
                                @if(!$contest->finished && !$contest->declined)
                                    <a href="{{route($editRoute, ['id' => $contest->id])}}"
                                       class="challenge-btn challenge-btn--outline" data-toggle="tooltip" data-tooltip="Отредактировать">
                                        <i class="challenge-btn__icon" style="background-image: url('/img/pencil.svg')"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                </div>
                <div class="contest-overview__actions {{ !empty($deelsStudio) && $participationState['participating'] ? 'studio-is-participating' : '' }}">
                    @if(!empty($deelsStudio) && $participationState['participating'] && !$contest->finished)
                        <p class="studio-participation-note"><span aria-hidden="true">✓</span> Ты участвуешь в челлендже</p>
                    @endif
                    <div class="challenge-participation-actions {{$participationState['called'] ? 'challenge-participation-actions--invited' : ''}}">
                        @if(!empty($deelsStudio) && !$ownerId && !$contest->finished)
                            <a class="story__button story__button-purple challenge-btn w-100" href="{{ route('login') }}">Войти для участия <span aria-hidden="true">↗</span></a>
                        @elseif($participationState['action'] === 'accept')
                            <form method="POST" action="{{route('battles.participation.accept', ['id' => $contest->id])}}" class="w-100">
                                @csrf
                                <button class="story__button story__button_participate story__button-purple challenge-btn w-100" type="submit">Принять</button>
                            </form>
                            <button class="story__button story__button_decline challenge-btn js-participation-confirm"
                                    type="button"
                                    data-confirm-text="Вы уверены?"
                                    data-confirm-action="{{route('battles.participation.decline', ['id' => $contest->id])}}">
                                Отказаться
                            </button>
                        @elseif($participationState['action'] === 'join')
                            <form method="POST" action="{{route('contests.participation.join', ['type' => $contestType, 'id' => $contest->id])}}" class="w-100">
                                @csrf
                                <button class="story__button story__button_participate story__button-purple challenge-btn d-flex jc-center w-100" type="submit">Участвовать</button>
                            </form>
                        @elseif($participationState['action'] === 'rejoin')
                            <form method="POST" action="{{route('contests.participation.rejoin', ['type' => $contestType, 'id' => $contest->id])}}" class="w-100">
                                @csrf
                                <button class="story__button story__button_participate story__button-purple challenge-btn d-flex jc-center w-100" type="submit">Участвовать</button>
                            </form>
                        @elseif($participationState['action'] === 'leave')
                            <button class="story__button story__button_participate story__button-purple challenge-btn story__button-outline w-100 js-participation-confirm"
                                    type="button" data-confirm-text="{{$leaveConfirmation}}"
                                    data-confirm-action="{{route('contests.participation.leave', ['type' => $contestType, 'id' => $contest->id])}}">
                                Выйти из участия
                            </button>
                        @elseif(!$hideClosedBattleAction)
                            <button class="story__button story__button_participate story__button-purple w-100" type="button" disabled>
                                {{$participationState['label']}}
                            </button>
                        @endif
                    </div>
                    @include('challenges.partials.page_invite', ['inviteWithShare' => true])
                    @if($isOwner && !$isBattle && $contest->finished && $contest->winner_selection === 'creator' && $contest->winner_selection_status === 'pending')
                        <button class="challenge-btn challenge-btn--fill contest-winner-selection-action js-open-winner-selection" type="button">
                            <span>Выбрать победителя</span>
                        </button>
                    @endif
                    @include('challenges.partials.reporting_controls', ['compact' => true])
                    @if(!empty($deelsStudio) && $reportingState['visible'] && !$reportingState['available'])
                        <p class="studio-action-hint">Добавление результата будет доступно в период проведения челленджа.</p>
                    @elseif(!empty($deelsStudio) && $reportingState['visible'] && $reportingState['checkin'] === 'story' && !$reportingState['story_allowed'])
                        <p class="studio-action-hint">Лимит видео за текущий период исчерпан. Твои результаты сохранены в журнале.</p>
                    @endif
                </div>
            </aside>
            <div class="contest-overview__content">
                @if(!empty($deelsStudio))
                    <div class="studio-contest-heading">
                        <div class="studio-contest-labels"><span class="studio-type-label">Челлендж</span><span class="studio-status {{ $contest->finished ? 'studio-status--finished' : '' }}">{{ $contest->status_title }}</span></div>
                        <h1 class="contest-overview__title">{{ $contest->title }}</h1>
                        <a class="studio-author" href="{{ route('user.profile', $contest->user->id) }}">
                            @if($contest->user->avatar_url)<img src="{{ $contest->user->avatar_url }}" width="38" height="38" alt="">@endif
                            <span><small>Автор челленджа</small><strong>{{ $contest->user->fullname ?: $contest->user->name }}</strong></span>
                        </a>
                    </div>
                @else
                    <h1 class="contest-overview__title">{{$contest->title}}</h1>
                @endif
                <dl class="contest-params">
                    @if(empty($deelsStudio))
                        <div class="contest-param"><dt>Автор</dt><dd><a class="contest-user-link" href="{{route('user.profile', $contest->user->id)}}">{{$contest->user->fullname ?: $contest->user->name}}</a></dd></div>
                        <div class="contest-param"><dt>Статус</dt><dd>{{$contest->status_title}}</dd></div>
                    @endif
                    <div class="contest-param"><dt>Период проведения</dt><dd>@if($periodStart){{\Carbon\Carbon::parse($periodStart)->format('d.m.y')}}@endif @if($periodStart && $periodFinish) - @endif @if($periodFinish){{\Carbon\Carbon::parse($periodFinish)->format('d.m.y')}}@endif</dd></div>
                    @unless($isBattle)
                        <div class="contest-param"><dt>Число участников</dt><dd>@if($participantLimit > 0){{$participantsCount}} / {{$participantLimit}}@if($remainingPlaces > 0 && $remainingPlaces < 20 && $recruitmentOpen) <span class="contest-param__urgent">(осталось {{$remainingPlaces}})</span>@endif @elseif(!empty($deelsStudio)){{ $participantsCount }} <small>без ограничения</small>@else ∞ @endif</dd></div>
                    @endunless
                    <div class="contest-param"><dt>Ритм</dt><dd>{{$rhythmLabels[$contest->rhythm] ?? 'Каждый день'}}</dd></div>
                    <div class="contest-param"><dt>{{ !empty($deelsStudio) ? 'Формат результата' : 'Чек-ин' }}</dt><dd>{{$checkinLabels[$contest->checkin] ?? 'Сторис'}}</dd></div>
                    @if(!empty($deelsStudio))
                        <div class="contest-param"><dt>Стоимость участия</dt><dd>{{ (float) $contest->cost > 0 ? rtrim(rtrim(number_format((float) $contest->cost, 2, ',', ' '), '0'), ',') . ' DEELS' : 'Бесплатно' }}</dd></div>
                    @endif
                    <div class="contest-param"><dt>Выбор победителя</dt><dd>{{$winnerSelectionLabels[$contest->winner_selection ?: 'likes']}}</dd></div>
                    @if((int) ($contest->reward_amount ?: $contest->amount) > 0)
                        <div class="contest-param contest-param--reward"><dt>Награда</dt><dd>{{number_format((int) ($contest->reward_amount ?: $contest->amount), 0, ',', ' ')}} DEELS</dd></div>
                    @endif
                    @if($topWinners->isNotEmpty())
                        <div class="contest-param">
                            <dt>{{$topWinners->count() === 1 ? 'Победитель' : 'Победители'}}</dt>
                            <dd class="contest-winners">
                                @foreach($topWinners as $winner)
                                    <a class="contest-user-link contest-winner-item" href="{{route('user.profile', $winner->id)}}" title="{{$winner->fullname ?: $winner->name}}">
                                        <img class="contest-winner-avatar" src="{{$winner->avatar_url}}" alt="{{$winner->fullname ?: $winner->name}}">
                                        @if($topWinners->count() === 1){{$winner->fullname ?: $winner->name}}@endif
                                    </a>
                                @endforeach
                                <span class="challenge-invite-more contest-winners-more" style="display: none"></span>
                            </dd>
                        </div>
                    @endif
                </dl>
                <div class="contest-description-wrap">
                    @if(!empty($deelsStudio))<h2 class="studio-task-title">Твоё задание</h2>@endif
                    <div class="contest-description js-contest-description">{!! nl2br(e($contest->description)) !!}</div>
                    <button class="contest-description-toggle js-contest-description-toggle" type="button" aria-expanded="false">Показать</button>
                </div>
            </div>
        </div>

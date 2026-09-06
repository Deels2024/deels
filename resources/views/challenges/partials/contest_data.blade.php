<section class="challenge pb-8">
    <div class="challenge-top">

        <style>
            @media screen and (max-width: 400px) {
                .catalog {
                    padding-top: 100px;
                }
            }
            body{
                /*background-image: url(/dist/images/challendge_desctop.webp);*/
            }
            @media screen and (max-width: 480px) {
                body{
                    /*background-image: url(/dist/images/challendge_mobile.webp);*/
                }
            }
            @media (max-width: 400px) {
                .challenge-grid {
                    grid-template-columns:  1fr;
                    gap: 10px;
                }
            }
            @media (max-width: 780px) {
                .copystories-btn {
                    display: block;
                }
            }
            .copystories-item__info {
                margin-bottom: 0 !important;
            }
            .copystories-winner-plate{
                position: absolute;
                bottom: 10%;
                color: #02ECFD;
                font-size: 1.25rem;
                border-radius: 12px;
                background-color: rgba(0, 0, 0, 0.7);
                padding: 0.7rem 2rem;
            }
            .challange-winner{
                border: 2px solid #02ECFD;
            }
            .challenge-card__head img {
                height: 60px;
                width: 60px;
            }
            .copystories-item__info{
                gap:1rem;
                margin-bottom: 6.25rem ;
            }
            .play-btn{
                background: none;
                border-radius: 0;
                width: 4.25rem;
                height: 4.25rem;
            }
            .play-btn::after{
                background-image: url(/dist/images/icons/play_button.svg);
                background-size: contain;
            }
            .copystories-info--views::before{
                background-image: url(/dist/images/icons/play_mini.svg);
            }
            .copystories-info--likes::before{
                background-image: url(/dist/images/icons/heart_mini.svg);
            }
            .copystories-info--comments::before{
                background-image: url(/dist/images/icons/chat_mini.svg);
            }
            .copystories-info{
                font-size: 1.25rem;
            }
            .js-delete-report-story {
                display: inline-flex;
                gap: 5px;
                margin-left: 5px;
                align-items: center;
            }
            @font-face {
                font-family: 'Gilroy';
                src: url("/dist/css/font/Gilroy-Light.woff2") format("woff2"), url("/dist/css/font/Gilroy-Light.woff") format("woff");
                font-weight: 300;
                font-style: normal;
                font-display: swap;
            }
            .challenge-top{
                padding: 0;
            }
            .text-thin{
                font-weight: 300;
            }
            .text-purple{
                color:#B27DFF;
            }
            .story__button {
                font-size: 16px;
            }
            .story__button-purple{
                background-color:#8D46F6;
                width: 10.5rem;
                height: 50px;
            }
            .story__button-gray{
                background-color: #7a7a7a;
            }
            .story__button-outline {
                position: relative;
                background: #0d102c;
                background-clip: padding-box;
                border: solid 2px transparent;
                border-left: 0;
                border-right: 0;
            }
            .story__button-outline::before {
                content: "";
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: -1;
                margin: -2px;
                background: none;
                border-radius: inherit;
                background: linear-gradient(90deg, #b224ef 0%, #7579ff 100%);
            }


            .story__button_participate{
                width: 16.5rem;
            }
            .story__button_participate[disabled] {
                cursor: default;
                opacity: .65;
            }
            .challenge-participation-actions {
                display: flex;
                gap: .75rem;
                width: 100%;
            }
            .challenge-participation-actions--invited > form,
            .challenge-participation-actions--invited > .story__button_participate,
            .challenge-participation-actions--invited > .story__button_decline {
                width: 50%;
            }
            .challenge-participation-actions--invited > form .story__button_participate {
                width: 100%;
            }
            .story__button_decline {
                background: #d9364f;
            }
            .participation-confirm {
                position: fixed;
                inset: 0;
                z-index: 10000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(0, 0, 0, .72);
            }
            .participation-confirm.is-open {
                display: flex;
            }
            .participation-confirm__dialog {
                width: min(34rem, 100%);
                padding: 2rem;
                border: 1px solid #8D46F6;
                border-radius: 1rem;
                background: #0D102C;
                text-align: center;
            }
            .participation-confirm__actions {
                display: flex;
                justify-content: center;
                gap: 1rem;
                margin-top: 1.5rem;
            }
            .contest-reporting {
                margin-top: 2rem;
                padding: 1.5rem;
                border: 1px solid rgba(178, 125, 255, .65);
                border-radius: 1rem;
                background: rgba(13, 16, 44, .85);
            }
            .contest-reporting__form {
                display: flex;
                flex-wrap: wrap;
                gap: .75rem;
                align-items: center;
            }
            .contest-reporting__input {
                width: min(18rem, 100%);
                min-height: 3rem;
                padding: .75rem 1rem;
                border: 1px solid #8D46F6;
                border-radius: .5rem;
                color: #fff;
                background: #0D102C;
            }
            .contest-reporting__notice {
                text-align: center;
                display: none;
                margin-top: 1rem;
                padding: .75rem 1rem;
                border-radius: .5rem;
                color: #07120a;
            }
            .contest-reporting__notice--sent { background: #62d98b; }
            .contest-reporting__notice--updated { background: #f3cf55; }
            .contest-reporting__history {
                display: grid;
                gap: .5rem;
                margin-top: 1.25rem;
            }
            .contest-reporting__deleted { color: #aeb0bd; }
            .contest-reporting .challenge-btn[disabled],
            .contest-reporting .challenge-btn.disabled,
            .contest-reporting__controls .challenge-btn[disabled],
            .contest-reporting__controls .challenge-btn.disabled {
                opacity: .5;
                cursor: not-allowed;
                pointer-events: none;
                filter: grayscale(.45);
            }
            .contest-reporting__controls--compact {
                width: 100%;
                margin-top: .75rem;
            }
            .contest-reporting__controls--compact .contest-reporting__form,
            .contest-reporting__controls--compact .challenge-btn {
                width: 100%;
            }
            .challenge-info__item{
                max-width: 22.5rem;
            }
            .challenge-top__row{
                display: grid;
                grid-template-columns: min-content 1fr 1fr;
                grid-template-rows: 0fr min-content 1fr 0fr;
                grid-column-gap: 1.635rem;
                grid-row-gap: 1.5rem;
            }
            .challenge-info__media{
                grid-row: 1/-1;
                grid-column:  1/2;
                width: 16.5rem;
                position: relative;
            }
            .challenge-name{
                grid-row: 1/2;
                grid-column:  2/3;
            }
            .challenge-deadlines{
                grid-row: 2/3;
                grid-column:  2/3;
            }
            .challenge-conditions{
                grid-row: 3/4;
                grid-column:  2/3;
            }
            .challenge-info__buttons{
                grid-row: 4/5;
                grid-column:  2/3;
            }
            .challenge-reward{
                grid-row: 1/2;
                grid-column:  3/4;
            }
            .challenge-status{
                grid-row: 2/3;
                grid-column:  3/4;
            }
            .challenge-description{
                grid-row: 3/4;
                grid-column:  3/4;
            }
            .contest-overview {
                display: grid;
                grid-template-columns: 16.5rem minmax(0, 1fr);
                gap: 2rem;
                align-items: start;
                min-width: 0;
            }
            .account-main, .account-info, .challenge { min-width: 0; max-width: 100%; }
            .contest-overview__aside { position: relative; display: grid; gap: .8rem; }
            .contest-overview__aside > .challenge-info__media { grid-row: 1; }
            .contest-overview__aside > .contest-overview__actions { grid-row: 2; }
            .contest-overview__content { min-width: 0; }
            .challenge-media-actions {
                display: grid;
                gap: .65rem;
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 5;
            }
            .story__button_share-link {
                min-width: auto!important;
            }
           .challenge-media-actions .challenge-btn {
                width: 100%;
                min-width: 0;
                padding-inline: .75rem;
            }
            .contest-overview__title {
                margin: 0 0 1.25rem;
                font-size: clamp(1.75rem, 3vw, 2.65rem);
                line-height: 1.08;
            }
            .contest-params {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .65rem 2rem;
                margin: 0 0 1.25rem;
            }
            .contest-param {
                display: grid;
                grid-template-columns: minmax(7.5rem, .8fr) minmax(0, 1.2fr);
                gap: .75rem;
                align-items: center;
                padding-bottom: .55rem;
                border-bottom: 1px solid rgba(255,255,255,.13);
            }
            .contest-param dt { color: rgba(255,255,255,.62); font-weight: 300; }
            .contest-param dd { margin: 0; text-align: right; }
            .contest-param__urgent { color: #ff5f72; white-space: nowrap; }
            .contest-user-link { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 5px}
            .contest-user-link:hover { color: #b27dff; }
            .contest-winners { display: inline-flex; justify-content: flex-end; align-items: center; gap: .35rem; flex-wrap: nowrap; min-width: 0; width: 100%; overflow: hidden; }
            .contest-winner-item.is-overflow-hidden { display: none; }
            .contest-winner-avatar { width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; }
            .contest-description { position: relative; overflow: hidden; font-weight: 300; line-height: 1.55; word-break: break-word;}
            .contest-description.is-collapsed {
                max-height: var(--contest-description-max-height, 12rem);
                -webkit-mask-image: linear-gradient(to bottom, #000 0, #000 72%, transparent 100%);
                mask-image: linear-gradient(to bottom, #000 0, #000 72%, transparent 100%);
            }
            .contest-description-toggle {
                display: none; margin-top: .5rem; padding: 0; border: 0;
                color: #b27dff; background: transparent; text-decoration: underline;
            }
            .contest-overview__actions {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: .65rem;
            }
            .contest-overview__actions > .challenge-participation-actions,
            .contest-overview__actions > .contest-reporting__controls {
                grid-column: 1 / -1;
            }
            .contest-overview__actions > .contest-winner-selection-action {
                grid-column: 1 / -1;
                width: 100%;
            }
            .contest-action-small { min-width: 0; width: 100%; height: 3.4rem; padding: .5rem; }
            .contest-action-small span { white-space: nowrap; }
            .contest-page-invite { display: none; margin-top: 0 !important; }
            .contest-overview__actions > .contest-page-invite {
                display: block;
                min-width: 0;
            }
            .contest-page-invite.is-visible { display: block; }
            .challenge-top__row { display: none !important; }
            .useful-story { position: relative; }
            .useful-story__remove {
                position: absolute; top: .65rem; right: .65rem; z-index: 20;
                display: inline-flex; align-items: center; justify-content: center;
                width: 2.25rem; height: 2.25rem; border: 1px solid rgba(255,255,255,.35);
                border-radius: 50%; color: #fff; background: rgba(13,16,44,.94);
                box-shadow: 0 4px 14px rgba(0,0,0,.35); cursor: pointer;
            }
            .useful-story__remove:hover { background: #d9364f; }
            .battle-reports { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
            .battle-reports__column { min-width: 0; }
            .battle-reports__head {
                position: sticky; top: 0; z-index: 1; display: flex; align-items: center;
                gap: .75rem; margin-bottom: 1rem; padding: .75rem;
                border-radius: .75rem; background: rgba(13,16,44,.96);
            }
            .battle-reports__avatar { width: 3rem; height: 3rem; border-radius: 50%; object-fit: cover; }
            .battle-reports__stats { margin-left: auto; color: rgba(255,255,255,.65); font-size: .85rem; text-align: right; display: flex; gap: 10px}
            .battle-reports__stats .copystories-info {
                font-size: 14px;
                gap: 3px;
            }
            .battle-reports__stats .copystories-info:before {
                width: 12px;
                height: 12px;
                background-size: contain;
            }
            .battle-reports__stories { display: grid; gap: 1rem; }
            .battle-reports__stories .challenge-card {
                width: 100%;
                max-width: 280px;
                margin-inline: auto;
            }
            .contest-journal { margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,.35); }
            .contest-journal__title { margin: 0 0 1.5rem; text-align: center; }
            .contest-journal__scroll {
                max-width: 100%;
                overflow-x: auto;
                overflow-y: auto;
                padding-bottom: 4px;
                scrollbar-width: thin;
                scrollbar-color: #00F0FF rgba(255,255,255,.1);
                overscroll-behavior-inline: contain;
                -webkit-overflow-scrolling: touch;
            }
            .contest-journal__scroll::-webkit-scrollbar { width: 3px; height: 3px; }
            .contest-journal__scroll::-webkit-scrollbar-track { background-color: rgba(255,255,255,.1); }
            .contest-journal__scroll::-webkit-scrollbar-thumb {
                border-radius: 10px;
                background-color: #00F0FF;
            }
            .contest-journal__table {
                width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0;
                color: #fff; background: rgba(13,16,44,.55);
            }
            .contest-journal__table th,
            .contest-journal__table td {
                min-width: 7.5rem; height: 5.5rem; padding: .6rem;
                border-right: 1px solid rgba(255,255,255,.18);
                border-bottom: 1px solid rgba(255,255,255,.18);
                text-align: center; vertical-align: middle;
            }
            .contest-journal__table thead th {
                position: sticky; top: 0; z-index: 4; height: auto; min-height: 3.5rem;
                background: #171738; white-space: nowrap;
            }
            .contest-journal__table .contest-journal__person {
                position: sticky; left: 0; z-index: 3; min-width: 11rem; max-width: 11rem;
                background: #171738; text-align: left;
            }
            .contest-journal__table thead .contest-journal__person { z-index: 6; }
            .contest-journal__table .contest-journal__total {
                position: sticky; left: 11rem; z-index: 3; min-width: 7rem;
                background: #171738;
            }
            .contest-journal__table thead .contest-journal__total { z-index: 6; }
            .contest-journal__table td:has([data-toggle="tooltip"]:hover),
            .contest-journal__table td:has([data-toggle="tooltip"]:focus) {
                position: relative;
                z-index: 8;
            }
            .contest-journal__table [data-toggle="tooltip"]:hover,
            .contest-journal__table [data-toggle="tooltip"]:focus {
                z-index: 9;
            }
            [data-toggle="tooltip"][data-tooltip]::before {
                content: attr(data-tooltip);
            }
            .contest-journal__user { display: flex; align-items: center; gap: .65rem; }
            .contest-journal__rank { width: 1.2rem; text-align: right; color: rgba(255,255,255,.6); }
            .contest-journal__avatar { width: 2.8rem; height: 2.8rem; border-radius: 50%; object-fit: cover; }
            .contest-journal__avatar.is-late { filter: grayscale(1); opacity: .45; }
            .contest-journal__missed { color: #fff; background: rgba(201,50,71,.72); font-size: 1.5rem; }
            .contest-journal__story {
                display: inline-block; width: 3.25rem; aspect-ratio: 9/16; overflow: hidden;
                border-radius: .45rem; vertical-align: middle;
            }
            .contest-journal__story img,
            .contest-journal__story video { width: 100%; height: 100%; object-fit: cover; }
            .contest-journal__mark { color: #74e59a; font-size: 1.65rem; }
            @media screen and (max-width: 1200px) {
                .contest-params { grid-template-columns: 1fr; }
                .challenge-top__row{
                    grid-template-columns: min-content 1fr;
                    grid-template-rows: min-content min-content min-content min-content;
                }
                .challenge-info__media{
                    grid-row: 2/5;
                    grid-column:  1/2;
                    /*width: 13.5rem;*/
                }
                .challenge-name{
                    grid-row: 1/2;
                    grid-column:  1/-1;
                }
                .challenge-deadlines{
                    grid-row: 6/7;
                    grid-column:  1/-1;
                }
                .challenge-conditions{
                    grid-row: 5/6;
                    grid-column:  1/-1;
                }
                .challenge-info__buttons{
                    grid-row: 7/8;
                    grid-column:  1/-1;
                }
                .challenge-reward{
                    grid-row: 2/3;
                    grid-column:  2/3;
                }
                .challenge-status{
                    grid-row: 3/4;
                    grid-column:  2/3;
                }
                .challenge-description{
                    grid-row: 4/5;
                    grid-column:  2/3;
                }
            }
            @media (max-width: 786px) {

                .battle-reports { grid-template-columns: 1fr; }
                .challenge-info__media {
                    max-width: 100%;
                }
            }
            @media (max-width: 600px) {
                .contest-overview { grid-template-columns: 1fr; }
                .challenge-info__media {
                    /*max-width: 10.5rem;*/
                    width: 100%;
                }
                .contest-overview__aside {
                    align-items: center;
                    justify-content: center;
                }
                .story__button-purple {
                    width: 100% !important;
                    max-width: none;
                }
            }

            @media screen and (max-width: 450px) {
                .coin{
                    width: 1.5rem;
                    height: 1.5rem;
                }
                .challenge-top__row{
                    grid-column-gap: 0.75rem;
                }
                .text-thin{
                    font-size: 0.85rem;
                }
                .story__button_participate{
                    font-size: 16px;
                }
                .story__button_copy-link{
                    width: 4.75rem;
                    min-width: 0;
                }
            }
            @media screen and (max-width: 450px) {
                .challenge-card {
                    max-width: 363px;
                }
            }
        </style>

        @php
            $mainStory = $contest->getMainStory()->first();
            $isBattle = $contestType === 'battle';
            $routeParam = $isBattle ? 'battle' : 'challenge';
            $ownerId = Auth::user()->id ?? null;
            $isOwner = Auth::user() && $ownerId == $contest->user_id;
            $participationState = app(\App\Services\Contests\ContestParticipationService::class)
                ->state($contest, $contestType, $ownerId);
            $hideClosedBattleAction = $isBattle
                && ($participationState['action'] ?? null) === 'disabled'
                && ($participationState['label'] ?? null) === 'Набор закрыт';
            $reportingState = $ownerId
                ? app(\App\Services\Contests\ContestReportingService::class)
                    ->state($contest, $contestType, (int) $ownerId)
                : ['visible' => false];
            $leaveConfirmation = $isBattle
                ? 'Вы уверены? Выход будет расценен как проигрыш'
                : ($participationState['singleAuthor']
                    ? 'Вы уверены, что хотите завершить челлендж? Это действие будет расценено как проигрыш'
                    : 'Вы уверены? <br>Возможно, вы не сможете снова присоединиться к челленджу');
        @endphp

        @php
            $participantsCount = (int) $contest->participants;
            $participantLimit = (int) ($contest->participants_count ?: 0);
            $remainingPlaces = max(0, $participantLimit - $participantsCount);
            $recruitmentOpen = !$contest->started && !$contest->finished;
            $periodStart = $contest->date_from ?: $contest->start;
            $periodFinish = $contest->date_to ?: $contest->finish;
            $rhythmLabels = ['once' => '1 раз', 'daily' => 'Каждый день', 'three_days' => 'Раз в 3 дня'];
            $checkinLabels = ['story' => 'Сторис', 'button' => 'Отметка', 'value' => 'Числовое значение'];
            $winnerSelectionLabels = ['likes' => 'По лайкам', 'creator' => 'По решению создателя'];
            $topWinners = $contest->finished ? $contest->winners : collect();
        @endphp
        @include('challenges.partials.contest_overview')
        <div class="challenge-top__row d-grid">
            <div class="challenge-info__media">
                @if($mainStory)
                    <a href="#story-popup"
                       class="challenge-media challenge-media--play show_story"
                       data-route="{{route('stories.preview', ['id' => $mainStory->id, 'user_id' => $ownerId])}}"
                       data-story="{{$mainStory->id}}"
                       data-type="{{$mainStory->type}}"
                       data-paid="{{$mainStory->paid}}"
                       data-amount="{{$mainStory->amount}}">
                        @include('challenges.partials.contest_media', ['contest' => $contest])
                        @include('challenges.partials.contest_finished_badge', ['contest' => $contest, 'contestTitle' => $contestTitle])
                    </a>
                @else
                    <div class="challenge-media">
                        @include('challenges.partials.contest_media', ['contest' => $contest])
                        @include('challenges.partials.contest_finished_badge', ['contest' => $contest, 'contestTitle' => $contestTitle])
                    </div>
                @endif
            </div>
            <h2 class="challenge-name"><div class="text-accent">Название:</div>{{$contest->title}}</h2>
            @if(!$contest->finished && $contest->daysLeft())
                <div class="challenge-info__item challenge-deadlines">
                    <h3 class="mb-1">До завершения:</h3>
                    <span class="mb-1 text-thin">{{$contest->daysLeft()}}</span>
                </div>
            @endif

            <div class="challenge-info__item challenge-conditions">
                <h3 class="mb-1">Критерии победителя:</h3>
                @if(($contest->winner_selection ?: 'likes') === 'creator')
                    <span class="mb-1 d-inline-block text-thin">По решению создателя</span><br>
                    <i><small class="text-purple d-inline-block">Создатель выбирает победителя в течение 3 дней после окончания {{$contestGenitive}}. Если решение не принято, победитель определяется автоматически по лайкам.</small></i>
                @else
                    <span class="mb-1 d-inline-block text-thin">По лайкам</span><br>
                    <i><small class="text-purple d-inline-block">При наличии участников с одинаковым количеством лайков, сумма выигрыша распределяется между ними равномерно.</small></i>
                @endif
                @if($contest->min_participants)
                    <div class="min_participants"><span>Необходимое количество участников для старта {{$contestGenitive}}:</span> <div class="btn_pill">{{$contest->min_participants}}</div></div>
                @endif
                @if($contest->cost && $contest->cost > 0)
                    <div class="min_participants"><span>Стоимость участия:</span> <div class="btn_pill">{{number_format($contest->cost, 0)}} <img class="coin" src="/dist/images/deels_coin_large.png" srcset="/dist/images/deels_coin_large.png" alt="DEELS" style="width: 12px; height: auto; margin-left: 2px;"></div></div>
                @endif
            </div>
            <div class="challenge-info__buttons d-flex gap-3 mt-auto w-100">
                <button class="story__button story__button_share-link story__button-purple btn__copy" type="button"></button>
                <div class="challenge-participation-actions {{$participationState['called'] ? 'challenge-participation-actions--invited' : ''}}">
                    @if($participationState['action'] === 'accept')
                        <form method="POST" action="{{route('battles.participation.accept', ['id' => $contest->id])}}" class="w-100">
                            @csrf
                            <button class="story__button story__button_participate story__button-purple d-flex jc-center gap-2 w-100 text-thin" type="submit">
                                <img src="/images/icon/hand-up.svg" alt="">
                                Принять
                            </button>
                        </form>
                    @elseif($participationState['action'] === 'join')
                        <form method="POST" action="{{route('contests.participation.join', ['type' => $contestType, 'id' => $contest->id])}}" class="w-100">
                            @csrf
                            <button class="story__button story__button_participate story__button-purple d-flex jc-center gap-2 w-100 text-thin" type="submit">
                                <img src="/images/icon/hand-up.svg" alt="">
                                Участвовать
                            </button>
                        </form>
                    @elseif($participationState['action'] === 'rejoin')
                        <form method="POST" action="{{route('contests.participation.rejoin', ['type' => $contestType, 'id' => $contest->id])}}" class="w-100">
                            @csrf
                            <button class="story__button story__button_participate story__button-purple d-flex jc-center gap-2 w-100 text-thin" type="submit">
                                <img src="/images/icon/hand-up.svg" alt="">
                                Участвовать
                            </button>
                        </form>
                    @elseif($participationState['action'] === 'leave')
                        <button class="story__button story__button_participate story__button-purple d-flex jc-center story__button-outline gap-2 w-100 text-thin js-participation-confirm"
                                type="button"
                                data-confirm-text="{{$leaveConfirmation}}"
                                data-confirm-action="{{route('contests.participation.leave', ['type' => $contestType, 'id' => $contest->id])}}">
                            Выйти из участия
                        </button>
                    @elseif(!$hideClosedBattleAction)
                        <button class="story__button story__button_participate story__button-purple d-flex jc-center gap-2 w-100 text-thin" type="button" disabled>
                            {{$participationState['label']}}
                        </button>
                    @endif

                    @if($participationState['called'])
                        <button class="story__button story__button_decline d-flex jc-center w-100 text-thin js-participation-confirm"
                                type="button"
                                data-confirm-text="Вы уверены?"
                                data-confirm-action="{{route('battles.participation.decline', ['id' => $contest->id])}}">
                            Отказаться
                        </button>
                    @endif
                </div>
            </div>
            @if($contest->amount > 0)
                <h2 class="challenge-reward">
                    <div class="text-accent">Награда:</div>
                    <div class="d-flex gap-1 ai-center">{{number_format($contest->amount, 0, ',', ',')}} <img class="coin" src="/dist/images/deels_cur 1 1.png" srcset="/dist/images/deels_cur 1 1-maj.png 2x" alt="DEELS"></div>
                </h2>
            @endif
            <div class="challenge-info__item challenge-status">
                <h3 class="mb-1">Статус:</h3>
                {{$contest->status_title}}
            </div>
            <div class="challenge-info__item challenge-description">
                <h3 class="mb-1">Описание:</h3>
                <span class="mb-1 d-inline-block text-thin w-100">{!! nl2br($contest->description) !!}</span>
            </div>
        </div>
        @include('challenges.partials.page_invite')
        @if($isOwner && isset($show_owner_buttons))
            <div class="challenge-buttons">
                <a href="{{route('stories.create', [$routeParam => $contest->id, 'useful' => 1])}}"
                   class="challenge-btn challenge-btn--fill mt-auto">
                    <span>Добавить полезное</span>
                </a>
                @if(!$contest->finished)
                    <a href="{{route($stopRoute, ['id' => $contest->id])}}" class="challenge-btn challenge-btn--fill mt-auto">
                        <i class="challenge-btn__icon" style="background-image: url('/img/stop.svg')"></i>
                        <span>Завершить</span>
                    </a>
                @endif
                @if(!$contest->finished && !$contest->declined)
                    <a href="{{route($editRoute, ['id' => $contest->id])}}" class="challenge-btn challenge-btn--outline">
                        <i class="challenge-btn__icon" style="background-image: url('/img/pencil.svg')"></i>
                        <span>Отредактировать</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

        @if($reportingState['visible'] && isset($show_reports_block))
            <section class="contest-reporting" id="contest-reporting">
                <h3 class="mb-3">Отчётность</h3>
                <div class="text-thin mb-3">
                    Текущий период:
                    {{$reportingState['period_start']->format('d.m.Y')}}
                    @if(!$reportingState['period_start']->isSameDay($reportingState['period_end']))
                        — {{$reportingState['period_end']->format('d.m.Y')}}
                    @endif
                </div>

                <div class="contest-reporting__notice" role="status"></div>
                <div class="contest-reporting__history">
                    @foreach($reportingState['reports'] as $report)
                        <div data-report-id="{{$report->id}}">
                            {{$report->created_at->format('d.m.Y H:i')}} —
                            @if($report->kind === 'button')
                                ✓ Сделано
                            @elseif($report->kind === 'value')
                                <span class="js-contest-report-result">{{$report->value + 0}}</span>
                            @elseif(!$report->story_id)
                                <span class="contest-reporting__deleted">Сторис удалена</span>
                            @else
                                <a href="#story-popup" class="show_story"
                                   data-route="{{route('stories.preview', ['id' => $report->story_id, 'user_id' => $ownerId])}}"
                                   data-story="{{$report->story_id}}">Сторис #{{$report->story_id}}</a>
                                <button type="button" class="btn btn-small story_delete js-delete-report-story"
                                        data-story-id="{{$report->story_id}}"><span class="actions-link" style="background-image: url(/dist/images/admin_icons/icon-del.svg); margin-right:5px"></span>Удалить</button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="text-thin mt-3">
                    Суммарный результат: <span class="js-contest-report-total">{{$reportingState['total'] + 0}}</span>
                </div>
            </section>
        @endif

        @if($isOwner && !$isBattle && $contest->finished && $contest->winner_selection === 'creator' && $contest->winner_selection_status === 'pending')
            @php
                $winnerCandidateIds = app(\App\Services\Contests\ChallengeWinnerSelectionService::class)
                    ->eligibleWinnerUserIds($contest);
                $winnerCandidates = \App\Models\User::query()
                    ->whereIn('id', $winnerCandidateIds)
                    ->get()
                    ->sortBy(fn($user) => array_search((int) $user->id, $winnerCandidateIds, true))
                    ->values();
            @endphp
        <div class="participation-confirm" id="winner-selection-modal" aria-hidden="true">
            <div class="participation-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="winner-selection-title">
                <h3 id="winner-selection-title" class="mb-4">Выбор победителя</h3>
                @if($winnerCandidates->count())
                    <form id="winner-selection-form">
                        @csrf
                        <input type="hidden" name="challenge_id" value="{{$contest->id}}">
                        <div class="d-flex flex-column gap-3 mb-5" style="padding: 0 30px">
                            @foreach($winnerCandidates as $candidate)
                                <label class="text-thin" style="display: inline-flex; align-items: center; gap: 5px">
                                    <input type="checkbox" name="winner_user_ids[]" value="{{$candidate->id}}">
                                    <img src="{{$candidate->avatar_url}}"
                                         alt="{{$candidate->fullname ?: $candidate->name}}"
                                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover">
                                    <span>{{$candidate->fullname ?: $candidate->name}}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="participation-confirm__actions">
                            <button class="challenge-btn challenge-btn--fill" type="submit">Сохранить</button>
                            <button class="challenge-btn challenge-btn--outline js-close-winner-selection" type="button">Отмена</button>
                        </div>
                    </form>
                @else
                    <p class="text-thin mb-4">Нет участников для выбора победителя.</p>
                    <button class="challenge-btn challenge-btn--outline js-close-winner-selection" type="button">Закрыть</button>
                @endif
            </div>
        </div>
    @endif

    @php
        $winnerStories = collect();

        if ($contest->finished && $isBattle) {
            $battleWinnerStoriesQuery = \App\Models\Story::withoutGlobalScope('banned')
                ->with('user')
                ->withCount('likes')
                ->notMainStory()
                ->where('battle_id', $contest->id)
                ->where('broken', false);

            if (!empty($contest->loser_user_id)) {
                $winnerStories = $battleWinnerStoriesQuery
                    ->where('user_id', '!=', $contest->loser_user_id)
                    ->get()
                    ->unique('user_id')
                    ->values();
            } else {
                $battleWinnerStories = $battleWinnerStoriesQuery->get();
                $winnerStoryLikes = $battleWinnerStories->max('likes_count');

                if ($winnerStoryLikes > 0) {
                    $winnerStories = $battleWinnerStories
                        ->where('likes_count', $winnerStoryLikes)
                        ->unique('user_id')
                        ->values();
                }
            }

            if (!$winnerStories->count()) {
                $winnerUserIds = $contest->winners->pluck('id')->map(static fn ($userId) => (int) $userId)->all();

                if ($winnerUserIds) {
                    $winnerStories = \App\Models\Story::withoutGlobalScope('banned')
                        ->with('user')
                        ->notMainStory()
                        ->where('battle_id', $contest->id)
                        ->whereIn('user_id', $winnerUserIds)
                        ->where('broken', false)
                        ->get()
                        ->unique('user_id')
                        ->values();
                }
            }
        } elseif ($contest->finished) {
            $winnerStories = $contest->winners
                ->map(function ($winner) use ($contest) {
                    return \App\Models\Story::withoutGlobalScope('banned')
                        ->notMainStory()
                        ->where('challenge_id', $contest->id)
                        ->where('user_id', $winner->id)
                        ->first();
                })
                ->filter()
                ->values();
        }

        $winnersCount = $winnerStories->count();
    @endphp
    @if($winnersCount)
        <h4 class="challenge-subtitle text-accent mb-8 mt-8">{{$winnersCount == 1 ? 'Победитель' : 'Победители'}}
            {{$contestGenitive}}:</h4>

        <div class="challenge-grid" style="--challenge-grid: repeat(4, 1fr)">
            @foreach($winnerStories as $winnerStory)
                @include('stories.story_item', ['story' => $winnerStory, 'challenge' => true])
            @endforeach
        </div>
    @endif

    @php
        $usefulStories = $contest->usefulStories()
            ->where('stories.declined', false)
            ->whereNull('stories.blocked_at')
            ->whereNull('stories.withdrawn_at')
            ->where('stories.broken', false)
            ->latest()
            ->get();
    @endphp
    @if($usefulStories->isNotEmpty())
        <section class="useful-stories-section">
        <h4 class="challenge-subtitle mb-8 mt-8">Полезное</h4>
        <div class="challenge-grid useful-stories-grid owl-carousel owl-theme useful__carousel" style="--challenge-grid: repeat(4, 1fr)">
            @foreach($usefulStories as $usefulStory)
                <div class="useful-story" data-story-id="{{$usefulStory->id}}">
                    @if($isOwner)
                        <button class="useful-story__remove js-exclude-useful-story" type="button"
                                aria-label="Исключить сторис" title="Исключить сторис">×</button>
                    @endif
                    @include('stories.story_item', ['story' => $usefulStory, 'challenge' => true])
                </div>
            @endforeach
        </div>
        </section>
    @endif

    @if($participant && !$isBattle && isset($show_my_video))
        <h4 class="challenge-subtitle mb-8 mt-8">Твое видео для участия:</h4>
        <div class="d-flex ai-center jc-center">
            <div class="d-flex flex-column gap-6" style="max-width: 280px; width: 100%;">
                <a href="#story-popup" class="challenge-media challenge-media--video show_story"
                   data-route="{{route('stories.preview', ['id' => $participant->id, 'user_id' => $ownerId])}}"
                   data-story="{{$participant->id}}" data-type="{{$participant->type}}"
                   data-paid="{{$participant->paid}}" data-amount="{{$participant->amount}}">
                    @include('stories.parts.preview', ['story' => $participant])
                </a>
                <div style="text-align: center">
                    {!! $participant->frozen && !$participant->banned ? '<br>[ На проверке ]' : '' !!}
                    {!! $participant->banned ? '<br><span style="color:#ff0000">[ Заблокировано ]</span>' : '' !!}
                    @if($participant->banned)
                        <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                            {!! $participant->banned_reason ?? 'Бан за нарушение правил'!!}
                        </div>
                    @endif
                </div>
                <a href="{{route('stories.create',[$routeParam => $contest->id])}}" class="challenge-btn"
                   style="border: 1px solid;">Заменить</a>
            </div>
        </div>
    @endif

    @php
        $journalStories = collect($stories instanceof \Illuminate\Contracts\Pagination\Paginator ? $stories->items() : $stories ?? [])
            ->filter(fn($story) => $story && !$story->is_main_story && !$story->is_useful);
        $journalReports = \App\Models\ContestReport::with(['story.user', 'story.likes'])
            ->where('contest_type', $contestType)
            ->where('contest_id', $contest->id)
            ->where(function ($query) {
                $query->where('kind', '!=', 'story')
                    ->orWhereHas('story', fn($storyQuery) => $storyQuery->active());
            })
            ->orderBy('period_started_at')
            ->get();
        $journalUserIds = $journalReports->pluck('user_id')
            ->merge($journalStories->pluck('user_id'));
        if ($isBattle) {
            $journalUserIds = $journalUserIds->merge([$contest->user_id, $contest->called_user_id]);
        } elseif (\Illuminate\Support\Facades\Schema::hasTable('contest_participations')) {
            $journalUserIds = $journalUserIds->merge(
                \Illuminate\Support\Facades\DB::table('contest_participations')
                    ->where('contest_type', $contestType)
                    ->where('contest_id', $contest->id)
                    ->where('status', 'active')
                    ->pluck('user_id')
            );
        }
        $journalUsers = \App\Models\User::whereIn('id', $journalUserIds->filter()->unique())
            ->get()->keyBy('id');
        if ($isBattle) {
            $journalUsers = collect([$contest->user_id, $contest->called_user_id])
                ->filter()
                ->map(fn($userId) => $journalUsers->get((int) $userId))
                ->filter()
                ->keyBy('id');
        }
        $journalCheckin = (string) ($contest->checkin ?: 'story');
        $journalRhythm = $isBattle ? 'daily' : (string) ($contest->rhythm ?: 'daily');
        $journalIsGallery = !$isBattle
            && $journalCheckin === 'story'
            && ($journalRhythm === 'once' || $journalUsers->count() <= 1);
        $journalVisible = $journalStories->isNotEmpty() || $journalReports->isNotEmpty();
    @endphp

    @if(!empty($deelsStudio) && !$journalVisible)
        <section class="studio-responses-empty"><span class="hv2-section-kicker">Результаты участников</span><h2>Здесь появятся первые ответы</h2><p>После публикации доступные ответы участников будут показаны в журнале челленджа.</p></section>
    @endif

    @if($journalVisible)
        <section class="contest-journal">
            <h3 class="contest-journal__title">{{ !empty($deelsStudio) ? 'Результаты участников' : 'Наш журнал' }}</h3>

            @if($isBattle)
                @php
                    $battleStoryGroups = $journalStories->filter(fn($story) => $story->user)->groupBy('user_id');
                @endphp
                <div class="battle-reports">
                    @foreach($journalUsers->take(2) as $battleUser)
                        @php
                            $battleStories = $battleStoryGroups->get($battleUser->id, collect());
                        @endphp
                        <div class="battle-reports__column">
                            <header class="battle-reports__head">
                                <a href="{{route('user.profile', $battleUser->id)}}"><img class="battle-reports__avatar" src="{{$battleUser->avatar_url}}" alt=""></a>
                                <a class="contest-user-link" href="{{route('user.profile', $battleUser->id)}}">{{$battleUser->fullname ?: $battleUser->name}}</a>
                                <div class="battle-reports__stats">
                                    <div data-toggle="tooltip" data-tooltip="Лайков">
                                        <span class="copystories-info copystories-info--likes">{{$battleStories->sum(fn($story) => $story->likes->count())}}</span>
                                    </div>
                                    <div data-toggle="tooltip" data-tooltip="Сторис">
                                        <span class="copystories-info copystories-info--views">{{$battleStories->count()}}</span>
                                    </div>
                                </div>
                            </header>
                            <div class="battle-reports__stories">
                                @forelse($battleStories as $story)
                                    @include('stories.story_item', ['story' => $story, 'challenge' => true])
                                @empty
                                    <p class="text-thin">Пока нет сторис</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($journalIsGallery)
                <div class="challenge-grid" style="--challenge-grid: repeat(4, minmax(0, 280px))">
                    @forelse($journalStories as $story)
                        @include('stories.story_item', ['story' => $story, 'challenge' => true])
                    @empty
                        <p class="text-thin">Пока нет отчётов</p>
                    @endforelse
                </div>
            @else
                @php
                    $journalStart = \Carbon\Carbon::parse($contest->start ?: $contest->date_from ?: $contest->created_at)->startOfDay();
                    $journalEnd = \Carbon\Carbon::parse($contest->finish ?: $contest->date_to ?: now())->endOfDay();
                    $journalStep = $journalRhythm === 'three_days' ? 3 : 1;
                    $journalPeriods = collect();
                    for ($date = $journalStart->copy(); $date->lte($journalEnd); $date->addDays($journalStep)) {
                        $periodEnd = $date->copy()->addDays($journalStep)->subSecond()->min($journalEnd);
                        $journalPeriods->push(['key' => $date->toDateString(), 'start' => $date->copy(), 'end' => $periodEnd]);
                    }
                    $reportsByUserAndPeriod = $journalReports->groupBy(fn($report) => $report->user_id.'|'.$report->period_started_at->toDateString());
                    $journalRows = $journalUsers->map(function ($user) use ($journalReports, $journalCheckin) {
                        $userReports = $journalReports->where('user_id', $user->id);
                        $total = $journalCheckin === 'story'
                            ? $userReports->sum(fn($report) => $report->story ? $report->story->likes->count() : 0)
                            : ($journalCheckin === 'value' ? $userReports->sum('value') : $userReports->count());
                        return ['user' => $user, 'total' => $total];
                    })->sortByDesc('total')->values();
                @endphp
                <div class="contest-journal__scroll">
                    <table class="contest-journal__table">
                        <thead>
                        <tr>
                            <th class="contest-journal__person">Участник</th>
                            <th class="contest-journal__total">{{$journalCheckin === 'story' ? 'Лайки' : 'Суммарно'}}</th>
                            @foreach($journalPeriods as $period)
                                <th>
                                    {{$period['start']->translatedFormat('d M')}}
                                    @if(!$period['start']->isSameDay($period['end']))
                                        - {{$period['end']->translatedFormat('d M')}}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($journalRows as $index => $row)
                            @php
                                $missedPeriods = $journalPeriods
                                    ->filter(fn($period) => $period['end']->isPast())
                                    ->filter(function ($period) use ($row, $reportsByUserAndPeriod) {
                                        return !$reportsByUserAndPeriod->has($row['user']->id.'|'.$period['key']);
                                    });
                                $latestMissedPeriod = $missedPeriods->last();
                                $hasReportAfterMiss = $latestMissedPeriod
                                    && $journalReports
                                        ->where('user_id', $row['user']->id)
                                        ->contains(fn($report) => $report->created_at && $report->created_at->gt($latestMissedPeriod['end']));
                                $hasUnresolvedMiss = (bool) $latestMissedPeriod && !$hasReportAfterMiss;
                            @endphp
                            <tr>
                                <td class="contest-journal__person">
                                    <div class="contest-journal__user">
                                        <span class="contest-journal__rank">{{$index + 1}}</span>
                                        <a href="{{route('user.profile', $row['user']->id)}}" data-toggle="tooltip" data-tooltip="{{$row['user']->fullname ?: $row['user']->name}}">
                                            <img class="contest-journal__avatar {{$hasUnresolvedMiss ? 'is-late' : ''}}" src="{{$row['user']->avatar_url}}" alt="">
                                        </a>
                                    </div>
                                </td>
                                <td class="contest-journal__total">{{$row['total'] + 0}}</td>
                                @foreach($journalPeriods as $period)
                                    @php
                                        $cellReports = $reportsByUserAndPeriod->get($row['user']->id.'|'.$period['key'], collect());
                                        $missed = $cellReports->isEmpty() && $period['end']->isPast();
                                        $submittedLate = $cellReports->contains(
                                            fn($report) => $report->created_at && $report->created_at->gt($period['end'])
                                        );
                                        $keepMissedStyle = $missed || $submittedLate;
                                    @endphp
                                    <td class="{{$keepMissedStyle ? 'contest-journal__missed' : ''}}">
                                        @if($journalCheckin === 'story')
                                            @foreach($cellReports->whereNotNull('story_id') as $cellReport)
                                                @if($cellReport->story)
                                                    <a href="#story-popup" class="contest-journal__story show_story"
                                                       data-route="{{route('stories.preview', ['id' => $cellReport->story_id, 'user_id' => $ownerId])}}"
                                                       data-story="{{$cellReport->story_id}}">
                                                        @include('stories.parts.preview', ['story' => $cellReport->story])
                                                    </a>
                                                @endif
                                            @endforeach
                                            @if($missed)
                                                -
                                            @endif
                                        @elseif($journalCheckin === 'value')
                                            {{$cellReports->isNotEmpty() ? ($cellReports->last()->value + 0) : ($missed ? '-' : '')}}
                                        @else
                                            @if($cellReports->isNotEmpty())
                                                <span class="contest-journal__mark">✓</span>
                                            @elseif($missed)
                                                -
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <div class="d-flex flex-column gap-8 ai-center jc-center pt-8">
        <div class="account__pagination d-xs-none">
            {{$stories->links()}}
        </div>
        <a href="{{$route ?? route($backRoute)}}" class="d-xs-none challenge-btn challenge-btn--outline"
           style="min-width: 150px;">Назад</a>
    </div>
</section>

<div class="participation-confirm" id="participation-confirm" aria-hidden="true">
    <div class="participation-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="participation-confirm-text">
        <p id="participation-confirm-text"></p>
        <div class="participation-confirm__actions">
            <form method="POST" action="" id="participation-confirm-form">
                @csrf
                <button class="challenge-btn challenge-btn--fill" type="submit">Да</button>
            </form>
            <button class="challenge-btn challenge-btn--outline js-participation-cancel" type="button">Нет</button>
        </div>
    </div>
</div>

<style>
    .mobile_app_popup {
        h5 {
            margin-bottom: 20px!important;
            display: block!important;
        }
        .app_image {
            height: 40px!important;
            width: auto!important;
            margin: 5px!important;
        }
        .modal_close {
            display: block!important;
            margin-top: 20px!important;
            text-decoration: underline!important;
            opacity: .6!important;
        }
        .mobile_content {
            margin-top: 100px!important;
            border: 2px solid #fff!important;
            border-radius: 10px!important;
            padding: 20px!important;
            background-color: #0D102C!important;
            -webkit-box-shadow: 0 0 8px rgba(255, 255, 255, .5), inset 0 0 8px rgba(255, 255, 255, .5)!important;
            box-shadow: 0 0 8px rgba(255, 255, 255, .5), inset 0 0 8px rgba(255, 255, 255, .5)!important;
        }
    }
    .promo_popup_block {
        max-width: 80%;
        max-height: 710px;
    }
    .promo_popup_block img{
        position: relative;
    }
    .promo_popup_block .mfp-close {
        z-index: 3;
    }
    .promo_popup_block .story-media {
        text-align: center;
        background: transparent!important;
    }
</style>
<div class="story story--media demo-video mfp-hide promo_popup_block mobile_app_popup" id="mobile_app_popup">
    <div class="story-wrap">
        <div class="story-media">
            <div class="mobile_content">
                <h5>Скачайте наше приложение:</h5>
                <a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank"><img src="/images/promo/android.png" class="app_image"></a>
                <a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank"><img src="/images/promo/appstore.png" class="app_image"></a>
                <span class="modal_close">Остаться в браузере</span>
            </div>

        </div>
    </div>
</div>
@section('page-js')
<script type="text/javascript" src="{{ asset('/js/libs/jquery-cookies/jquery-cookies.js') }}"></script>
<script>
    @if(empty($deelsStudio) && !\Cookie::get('challenge_mobile_app'))
    $( document ).ready(function() {
        if ($(window).width() < 767) {
            $.cookie('challenge_mobile_app', true, {expires: 14, path: '/'});
            $('.modal_close').click(() => {
                $.magnificPopup.close({
                    items: {
                        src: '#mobile_app_popup'
                    },
                    type: 'inline'
                });
            });
            searchTimer = setTimeout(function () {
                $.magnificPopup.open({
                    items: {
                        src: '#mobile_app_popup'
                    },
                    type: 'inline'
                });
            }, 1000);
        }
    });
    @endif
    $(document).ready(function () {
        function fitContestDescriptions() {
            $('.js-contest-description').each(function () {
                var description = $(this);
                var toggle = description.next('.js-contest-description-toggle');
                var overview = description.closest('.contest-overview');
                var expanded = toggle.attr('aria-expanded') === 'true';
                var maxHeight = 192;

                description.removeClass('is-collapsed').css('--contest-description-max-height', '');

                if (overview.closest('.studio-challenge').length) {
                    maxHeight = 240;
                } else if (window.innerWidth > 786 && overview.length) {
                    var asideBottom = overview.find('.contest-overview__aside')[0].getBoundingClientRect().bottom;
                    var descriptionTop = description[0].getBoundingClientRect().top;
                    maxHeight = Math.max(72, Math.floor(asideBottom - descriptionTop - 32));
                }

                description.css('--contest-description-max-height', maxHeight + 'px');
                var needsCollapse = description[0].scrollHeight > maxHeight;
                toggle.toggle(needsCollapse);

                if (needsCollapse && !expanded) {
                    description.addClass('is-collapsed');
                } else if (!needsCollapse) {
                    toggle.text('Показать').attr('aria-expanded', 'false');
                }
            });
        }
        fitContestDescriptions();
        $(window).on('load.contestDescription', fitContestDescriptions);
        var contestDescriptionResizeTimer;
        $(window).on('resize.contestDescription', function () {
            window.clearTimeout(contestDescriptionResizeTimer);
            contestDescriptionResizeTimer = window.setTimeout(fitContestDescriptions, 100);
        });
        $(document).on('click', '.js-contest-description-toggle', function () {
            var toggle = $(this);
            var description = toggle.prev('.js-contest-description');
            var collapsed = description.toggleClass('is-collapsed').hasClass('is-collapsed');
            toggle.text(collapsed ? 'Показать' : 'Скрыть').attr('aria-expanded', collapsed ? 'false' : 'true');
        });
        $(document).on('click', '.js-exclude-useful-story', function () {
            if (!window.confirm('Вы уверены, что хотите исключить эту сторис из челленджа? Она исчезнет со страницы челленджа, но останется в вашем профиле')) {
                return;
            }
            var story = $(this).closest('.useful-story');
            $.post('{{route('stories.exclude_useful')}}', {
                _token: '{{csrf_token()}}',
                story_id: story.data('story-id')
            }).done(function () {
                story.remove();
                if (!$('.useful-stories-grid .useful-story').length) {
                    $('.useful-stories-section').remove();
                }
            }).fail(function () {
                $('.alert-container').html('<div class="alert danger"><span class="closebtn">&times;</span> Не удалось исключить сторис</div>');
            });
        });
        $(document).on("click", ".btn__copy", function (e) {
            $("body").append('<input id="copyURL" type="text" value="" />');
            $("#copyURL").val(window.location.href).select();
            document.execCommand("copy");
            $("#copyURL").remove();
            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span> Ссылка скопирована в буфер обмена</div>')
        });
        $(document).on('click', '.js-participation-confirm', function () {
            $('#participation-confirm-text').html($(this).data('confirm-text'));
            $('#participation-confirm-form').attr('action', $(this).data('confirm-action'));
            $('#participation-confirm').addClass('is-open').attr('aria-hidden', 'false');
        });
        $(document).on('click', '.js-participation-cancel', function () {
            $('#participation-confirm').removeClass('is-open').attr('aria-hidden', 'true');
        });
        $(document).on('click', '#participation-confirm', function (event) {
            if (event.target === this) {
                $(this).removeClass('is-open').attr('aria-hidden', 'true');
            }
        });
        $(document).on('input', '.js-contest-report-value', function () {
            var value = $(this).val();
            $('.js-contest-report-value').not(this).val(value);
            $('.js-contest-report-submit').each(function () {
                var submit = $(this);
                submit.prop(
                    'disabled',
                    Number(submit.data('reporting-available')) !== 1
                        || value === ''
                        || !Number.isFinite(Number(value))
                );
            });
        }).find('.js-contest-report-value').trigger('input');
        $(document).on('submit', '.js-contest-report-form', function (event) {
            event.preventDefault();
            var form = $(this);
            var button = form.find('button[type="submit"]');
            button.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function (data) {
                    var notice = $('.contest-reporting__notice');
                    notice
                        .removeClass('contest-reporting__notice--sent contest-reporting__notice--updated')
                        .addClass(data.updated ? 'contest-reporting__notice--updated' : 'contest-reporting__notice--sent')
                        .text(data.message)
                        .stop(true, true).fadeIn().delay(2500).fadeOut();
                    if (data.report && data.report.kind === 'value') {
                        $('.js-contest-report-value').val(Number(data.report.value));
                        var reportRow = $('.contest-reporting__history [data-report-id="' + data.report.id + '"]');
                        if (reportRow.length) {
                            reportRow.find('.js-contest-report-result').text(Number(data.report.value));
                        } else {
                            $('.contest-reporting__history').prepend(
                                $('<div>')
                                    .attr('data-report-id', data.report.id)
                                    .append(document.createTextNode(data.report.created_at + ' — '))
                                    .append($('<span class="js-contest-report-result">').text(Number(data.report.value)))
                            );
                        }
                        $('.js-contest-report-total').text(Number(data.total));
                    }
                    if (data.report && data.report.kind === 'button') {
                        $('.js-contest-report-button').prop('disabled', true);
                    }
                    if (form.find('.js-contest-report-value').length) {
                        $('.js-contest-report-value').first().trigger('input');
                    }
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    var message = errors ? Object.values(errors)[0][0] : 'Не удалось отправить результат';
                    $('.alert-container').html('<div class="alert danger"><span class="closebtn">&times;</span> '+message+'</div>');
                    if (form.find('.js-contest-report-value').length) {
                        $('.js-contest-report-value').first().trigger('input');
                    }
                }
            });
        });
        $(document).on('click', '.js-delete-report-story', function () {
            if (!window.confirm('Удалить сторис из {{$contestTitle === 'батл' ? 'батла' : 'челленджа'}}?')) {
                return;
            }
            var button = $(this);
            $.ajax({
                type: 'POST',
                url: '{{route('stories.remove')}}',
                data: {_token: '{{csrf_token()}}', story_id: button.data('story-id')},
                success: function (data) {
                    if (data.success) {
                        button.closest('div').html('<span class="contest-reporting__deleted">Сторис удалена</span>');
                        return;
                    }
                    $('.alert-container').html('<div class="alert danger"><span class="closebtn">&times;</span> '+(data.error || 'Не удалось удалить сторис')+'</div>');
                }
            });
        });
        $(document).on('click', '.js-open-winner-selection', function () {
            $('#winner-selection-modal').addClass('is-open').attr('aria-hidden', 'false');
        });
        $(document).on('click', '.js-close-winner-selection', function () {
            $('#winner-selection-modal').removeClass('is-open').attr('aria-hidden', 'true');
        });
        $(document).on('click', '#winner-selection-modal', function (event) {
            if (event.target === this) {
                $(this).removeClass('is-open').attr('aria-hidden', 'true');
            }
        });
        $(document).on('submit', '#winner-selection-form', function (event) {
            event.preventDefault();

            if (!$(this).find('input[name="winner_user_ids[]"]:checked').length) {
                $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> Выберите победителя</div>');
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{route('challenges.select_winners')}}',
                data: $(this).serialize(),
                success: function (data) {
                    if (data.success) {
                        window.location.reload();
                        return;
                    }
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+(data.error || 'Не удалось выбрать победителя')+'</div>');
                },
                error: function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Не удалось выбрать победителя';
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+message+'</div>');
                }
            });
        });

        function updateWinnersOverflow() {
            $('.contest-winners').each(function () {
                var container = $(this);
                var items = container.find('.contest-winner-item');
                var more = container.find('.contest-winners-more');

                items.removeClass('is-overflow-hidden');
                more.hide().text('');
                if (items.length <= 1) {
                    return;
                }

                more.text('+' + items.length).css('display', 'inline-flex');
                var available = container[0].clientWidth;
                var reserved = more.outerWidth(true) + 6;
                var used = 0;
                var hidden = 0;

                items.each(function () {
                    var item = $(this);
                    var width = item.outerWidth(true) + 6;
                    if (used + width <= available - reserved) {
                        used += width;
                    } else {
                        item.addClass('is-overflow-hidden');
                        hidden++;
                    }
                });

                if (hidden) {
                    more.text('+' + hidden);
                } else {
                    more.hide();
                }
            });
        }

        updateWinnersOverflow();
        $(window).off('resize.contestWinners').on('resize.contestWinners', updateWinnersOverflow);
    });
</script>
@endsection

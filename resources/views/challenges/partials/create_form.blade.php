@php
    $isBattle = $mode === 'battle';
    $item = $entity ?? null;
    $formAction = $item
        ? ($isBattle ? route('battles.update.web') : route('challenges.update.web'))
        : ($isBattle ? route('battles.store.web') : route('challenges.store.web'));
    $defaultParticipants = $isBattle ? 2 : 0;
    $titleLabel = $isBattle ? 'Название' : 'Название';
    $deelsBalance = intval(Auth::user()->wallet_balance ?? 0);
    $hasOldParticipantsVisual = old('participants_visual') !== null;
    $participantsValue = $hasOldParticipantsVisual ? old('participants_visual') : ($item->participants_count ?? $defaultParticipants);
    $participantsLimitValue = $hasOldParticipantsVisual ? old('min_participants_limit') : ($item->participants_count ?? '');
    if (!$hasOldParticipantsVisual && !$isBattle && $item && $item->participants_count > 1) {
        $participantsValue = 'limit';
    }
    $minParticipantsValue = old('min_participants') ?? $item->min_participants ?? (
        $isBattle
            ? 2
            : ((string) $participantsValue === '1'
                ? 1
                : ((string) $participantsValue === 'limit' ? ($participantsLimitValue ?: '') : 0))
    );
    $isSingleParticipant = !$isBattle && (string) $participantsValue === '1';
    $winnerSelection = old('winner_selection') ?? $item->winner_selection ?? 'likes';
    $selectedInviteIds = old('invite_user_ids') ?? $item->invite_user_ids ?? [];
    $selectedInviteIds = is_array($selectedInviteIds) ? array_filter($selectedInviteIds) : [];
    $calledUserId = old('called_user_id') ?? $item->called_user_id ?? '';
    $isEditing = (bool) $item;
    $hasStarted = $item && (bool) ($item->started ?? false);
    $winnerStartsAt = $item ? ($item->date_from ?: $item->start) : null;
    $winnerSelectionLocked = $item && (
        $hasStarted
        || ($winnerStartsAt && \Carbon\Carbon::parse($winnerStartsAt)->lte(\Carbon\Carbon::now()))
    );
    $participantUserIds = collect();
    if ($item) {
        $participantUserIds = $item->stories()
            ->withoutGlobalScopes()
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();
        $participantUserIds = $participantUserIds->unique()->values();
    }
    $actualParticipantsCount = $participantUserIds->count();
    $originalParticipantsMode = $isBattle
        ? '2'
        : ((int) ($item->participants_count ?? 0) === 1
            ? '1'
            : (((int) ($item->participants_count ?? 0) > 1) ? 'limit' : '0'));
    $originalParticipantsLimit = (int) ($item->participants_count ?? 0);
    $mainStory = $item ? $item->getMainStory()->first() : null;
    $hasMainStory = (bool) $mainStory;
    $mainStoryPreview = null;
    if ($mainStory) {
        $mainStoryPreview = $mainStory->type === 'video'
            ? ($mainStory->video_preview ?: $mainStory->path)
            : ($mainStory->thumbnail ?: $mainStory->file_path ?: $mainStory->path);
    }
    $hasCover = $item && $item->media_id;
    $coverPreview = $hasCover
        ? ($item->type === 'video'
            ? ($item->video_preview ?: $item->path)
            : ($item->thumbnail ?: $item->path))
        : null;
@endphp

<form id="startCampaignForm{{$isBattle ? 'Battle' : 'Challenge'}}" class="form-horizontal challenge-form" method="post" action="{{ $formAction }}" enctype="multipart/form-data" autocomplete="off" data-has-main-story="{{$hasMainStory ? '1' : '0'}}" data-has-cover="{{$hasCover ? '1' : '0'}}" data-edit-mode="{{$isEditing ? '1' : '0'}}" data-has-started="{{$hasStarted ? '1' : '0'}}" data-actual-participants="{{$actualParticipantsCount}}" data-original-participants-mode="{{$originalParticipantsMode}}" data-original-participants-limit="{{$originalParticipantsLimit}}">
    @csrf
    @if($item)
        <input type="hidden" name="{{$isBattle ? 'battle_id' : 'challenge_id'}}" value="{{$item->id}}">
    @endif

    <div class="challenge-create-layout" style="display: grid; grid-template-columns: minmax(0, 1fr) 210px; gap: 48px; align-items: start; width: 100%;">
        <div style="min-width: 0;">
            <label>
                <p>{{$titleLabel}} <span class="required_item">*</span></p>
                <input type="text" placeholder="Введите текст..." name="title" value="{{old('title') ?? $item->title ?? ''}}">
            </label>

            <label>
                <p>Описание <span class="required_item">*</span></p>
                <textarea rows="18" placeholder="Введите текст..." name="description" maxlength="5000" data-max-words="650" data-max-length="5000">{{old('description') ?? $item->description ?? ''}}</textarea>
                <span class="challenge-description-counter" aria-live="polite">0/5000</span>
            </label>

            <div class="d-flex flex-column gap-8 flex-wrap mb-8">
                <div class="challenge-date-row d-flex ai-center gap-3 flex-wrap">
                    <span>Срок </span>
                    <span>с</span>
                    <input type="text" name="date_from_visual" class="datetime-picker" aria-label="Дата начала" placeholder="ДД.ММ.ГГ ЧЧ:ММ" value="{{old('date_from_visual') ?? optional($item?->date_from)->format('d.m.y H:i')}}" data-original-value="{{optional($item?->date_from)->format('d.m.y H:i')}}" style="width: 150px; min-height: 28px; margin: 0;">
                    <span>до</span>
                    <input type="text" name="date_to_visual" class="datetime-picker" aria-label="Дата окончания" placeholder="ДД.ММ.ГГ ЧЧ:ММ" value="{{old('date_to_visual') ?? optional($item?->date_to)->format('d.m.y H:i')}}" data-original-value="{{optional($item?->date_to)->format('d.m.y H:i')}}" style="width: 150px; min-height: 28px; margin: 0;">
                </div>

                @if(!$isBattle && ($challange_coin ?? false))
                    <label class="challenge-coin-block d-flex ai-center gap-3 relative" style="width: 100%; margin: 0;">
                        <span>Челлендж-коин:</span>
                        <span class="coin-input">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" name="amount" min="100" max="10000" value="{{old('amount') ?? $item->amount ?? 100}}" data-coin {{$item ? 'readonly' : ''}} style="width: 140px; min-height: 28px; margin: 0;">
                        </span>
                    </label>
                @else
                    <input type="hidden" name="amount" value="{{old('amount') ?? $item->amount ?? 5000}}">
                @endif
            </div>
        </div>

        <div class="challenge-create-media" style="width: 210px;">
            <p class="mb-3">Обложка <span class="required_item">*</span></p>
            <label class="challenge-upload">
                <input
                        class="filePreviewUploadMain {{$hasCover ? '' : 'required_input'}}"
                        type="file"
                        name="mainImg"
                        accept="image/*,video/*"
                        data-preview-container="#previewContainerMain{{$isBattle ? 'Battle' : 'Challenge'}}"
                >
                <span class="form-file-area d-flex ai-center jc-center mb-8" @if($hasCover) style="display: none !important;" @endif>
                    <svg viewBox='0 0 300 100' preserveAspectRatio='none'>
                        <path d='M0,0 300,0 300,100 0,100z' vector-effect='non-scaling-stroke'/>
                    </svg>
                    <span>Нажмите, чтобы выбрать фото или видео (до 5 сек.)</span>
                </span>

                <div class="jc-center mb-2 upload_preview {{$hasCover ? 'active' : ''}}">
                    <div class="d-flex flex-column gap-3 mb-8 w-100">
                        <div class="new__img-main" id="previewContainerMain{{$isBattle ? 'Battle' : 'Challenge'}}" style="{{$hasCover ? '' : 'display: none'}}">
                            @if($hasCover && $coverPreview)
                                @if($item->type === 'video')
                                    <video src="{{$coverPreview}}" poster="{{$item->thumbnail}}" muted loop autoplay playsinline></video>
                                @else
                                    <img src="{{$coverPreview}}" alt="Обложка">
                                @endif
                            @endif
                        </div>
{{--                        <a href="#" class="preview_replace" style="border: 1px solid;font-size: 12px;text-align: center;border-radius: 5px;padding: 0.6em 1em;display: none">Заменить</a>--}}
                    </div>
                </div>
            </label>

            <p class="mb-3">Вводное сторис</p>
            <label class="challenge-upload">
                <input
                        class="filePreviewUploadStory"
                        type="file"
                        name="intro_story"
                        accept="image/*,video/*"
                        data-preview-container="#previewContainerStory{{$isBattle ? 'Battle' : 'Challenge'}}"
                >
                <span class="form-file-area d-flex ai-center jc-center mb-8" @if($hasMainStory) style="display: none !important;" @endif>
                    <svg viewBox='0 0 300 100' preserveAspectRatio='none'>
                        <path d='M0,0 300,0 300,100 0,100z' vector-effect='non-scaling-stroke'/>
                    </svg>
                    <span>Нажмите, чтобы выбрать фото или видео (до 5 сек.)</span>
                </span>

                <div class="d-flex jc-center mb-8">
                    <div class="flex-column gap-3 upload_preview mb-8 w-100 {{$hasMainStory ? 'active' : ''}}">
                        <div class="new__img-main" id="previewContainerStory{{$isBattle ? 'Battle' : 'Challenge'}}" style="{{$hasMainStory ? '' : 'display: none'}}">
                            @if($hasMainStory && $mainStoryPreview)
                                @if($mainStory->type === 'video')
                                    <video src="{{$mainStoryPreview}}" poster="{{$mainStory->thumbnail}}" muted loop autoplay playsinline></video>
                                @else
                                    <img src="{{$mainStoryPreview}}" alt="Вводное сторис">
                                @endif
                            @endif
                        </div>
{{--                        <a href="#" class="preview_replace" style="border: 1px solid;font-size: 12px;text-align: center;border-radius: 5px;padding: 0.6em 1em;display: none">Заменить</a>--}}
                    </div>
                </div>
            </label>
        </div>
    </div>

    <div style="width: 100%;">
        <input type="hidden" name="days" value="{{old('days') ?? $item->days ?? 1}}">
        <input type="hidden" name="cost" value="{{old('cost') ?? $item->cost ?? 0}}">
        <input type="hidden" name="min_participants" value="{{$minParticipantsValue}}">
        <input type="hidden" name="criteria[]" value="by_likes">

        <div class="challenge_settings mb-8">
            <div class="challenge_settings_item">
                <p class="mb-6 hint-block">Число участников</p>
                @if($isBattle)
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="participants_visual" value="2" {{(old('participants_visual') ?? $item->participants_count ?? 2) == 2 ? 'checked' : ''}}>
                        <span>2</span>
                    </label>
                @else
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="participants_visual" value="1" {{(string) $participantsValue === '1' ? 'checked' : ''}}>
                        <span>1</span>
                    </label>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="participants_visual" value="0" {{(string) $participantsValue === '0' ? 'checked' : ''}}>
                        <span>Не указывать</span>
                    </label>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="participants_visual" value="limit" {{(string) $participantsValue === 'limit' ? 'checked' : ''}}>
                        <span>До</span>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" name="min_participants_limit" min="2" max="100" value="{{$participantsLimitValue ?? 2}}" style="width: 70px; min-height: 28px; margin: 0;">
                    </label>
                @endif
            </div>

            <div class="challenge_settings_item">
                <p class="mb-6">Видимость</p>
                <label class="d-flex ai-center gap-3 mb-5">
                    <input type="radio" name="visibility" value="all" {{(old('visibility') ?? $item->visibility ?? 'all') === 'all' ? 'checked' : ''}}>
                    <span>Всем</span>
                </label>
                <label class="d-flex ai-center gap-3 mb-5">
                    <input type="radio" name="visibility" value="friends" {{(old('visibility') ?? $item->visibility ?? '') === 'friends' ? 'checked' : ''}}>
                    <span>Только друзьям участников</span>
                </label>
                <label class="d-flex ai-center gap-3 mb-5">
                    <input type="radio" name="visibility" value="participants" {{(old('visibility') ?? $item->visibility ?? '') === 'participants' ? 'checked' : ''}}>
                    <span>Только участникам</span>
                </label>
            </div>

            <div class="challenge_settings_item challenge-rhythm-block {{$winnerSelectionLocked ? 'is-inactive' : ''}}">
                <p class="mb-6">Ритм</p>
                    <input type="hidden" class="challenge-rhythm-hidden" name="rhythm_visual" value="{{old('rhythm_visual') ?? $item->rhythm ?? 'daily'}}" {{$winnerSelectionLocked ? '' : 'disabled'}}>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="rhythm_visual" value="once" {{(old('rhythm_visual') ?? $item->rhythm ?? '') === 'once' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>1 раз</span>
                    </label>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="rhythm_visual" value="daily" {{(old('rhythm_visual') ?? $item->rhythm ?? 'daily') === 'daily' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>Ежедневно</span>
                    </label>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="rhythm_visual" value="three_days" {{(old('rhythm_visual') ?? $item->rhythm ?? '') === 'three_days' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>Каждые 3 дня</span>
                    </label>
            </div>

            <div class="challenge_settings_item challenge-checkin-block {{$winnerSelectionLocked ? 'is-inactive' : ''}}" data-time-locked="{{$winnerSelectionLocked ? '1' : '0'}}">
                <p class="mb-6">Чек-ин</p>
                <input type="hidden" class="challenge-checkin-hidden" name="checkin_visual" value="{{old('checkin_visual') ?? $item->checkin ?? 'story'}}" {{$winnerSelectionLocked ? '' : 'disabled'}}>
                @if(!$isBattle)
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="checkin_visual" value="button" {{(old('checkin_visual') ?? $item->checkin ?? '') === 'button' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>По кнопке</span>
                    </label>
                    <label class="d-flex ai-center gap-3 mb-5">
                        <input type="radio" name="checkin_visual" value="value" {{(old('checkin_visual') ?? $item->checkin ?? '') === 'value' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>Ввод значения</span>
                    </label>
                @endif
                <label class="d-flex ai-center gap-3 mb-5">
                    <input type="radio" name="checkin_visual" value="story" {{(old('checkin_visual') ?? $item->checkin ?? 'story') === 'story' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                    <span>Онлайн-сторис</span>
                </label>
            </div>
        </div>

        <div class="challenge-extra-settings mb-8">
            <div class="challenge-extra-settings__item challenge-reward-block {{$isSingleParticipant || $winnerSelectionLocked ? 'is-inactive' : ''}}" data-deels-balance="{{$deelsBalance}}" data-time-locked="{{$winnerSelectionLocked ? '1' : '0'}}">
                <p class="mb-6">Награда</p>
                <label class="d-flex ai-center gap-3 mb-3" style="margin: 0;">
                    <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            name="reward_amount"
                            value="{{old('reward_amount') ?? $item->reward_amount ?? ''}}"
                            {{$isSingleParticipant || $winnerSelectionLocked ? 'readonly' : ''}}
                            style="width: 140px; min-height: 28px; margin: 0;"
                    >
                    <span class="wallet-info__balance"></span>
                </label>
                <p class="challenge-reward-balance">Ваш счет дилсов: {{number_format($deelsBalance, 0, ',', ' ')}}</p>
            </div>

            <div class="challenge-extra-settings__item challenge-winner-block {{$isSingleParticipant ? 'd-none' : ''}} {{$winnerSelectionLocked ? 'is-inactive' : ''}}" data-winner-locked="{{$winnerSelectionLocked ? '1' : '0'}}">
                <p class="mb-6">Выбор победителя</p>
                <input type="hidden" class="challenge-winner-hidden" name="winner_selection" value="{{$winnerSelection}}" {{$winnerSelectionLocked ? '' : 'disabled'}}>
                <label class="d-flex ai-center gap-3 mb-5 challenge-winner-by-likes">
                    <input type="radio" name="winner_selection" value="likes" {{$winnerSelection === 'likes' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                    <span>По лайкам</span>
                </label>
                @if(!$isBattle)
                    <label class="d-flex ai-center gap-3 challenge-winner-by-creator">
                        <input type="radio" name="winner_selection" value="creator" {{$winnerSelection === 'creator' ? 'checked' : ''}} {{$winnerSelectionLocked ? 'disabled' : ''}}>
                        <span>По решению создателя</span>
                    </label>
                @endif
            </div>
        </div>

        @if($isBattle)
            <div
                    class="challenge-call-block"
                    data-invite-url="{{route('challenges.invites.users')}}"
                    data-selected-id="{{$calledUserId}}"
            >
                <div class="d-flex ai-center gap-5 mb-8">
                    <p>Вызов</p>
                    <button class="challenge-btn challenge-btn--outline add_call" type="button">+</button>
                    <div class="challenge-call-selected"></div>
                </div>

                <div class="challenge-call-dropdown">
                    <button class="challenge-btn challenge-btn--outline challenge-call-random" type="button">Случайный пользователь</button>
                    <input type="text" class="challenge-call-search" placeholder="Поиск">

                    <div class="challenge-invite-section" data-section="call-friends">
                        <button class="challenge-invite-section__title" type="button">
                            <span>Друзья</span>
                            <span class="challenge-invite-section__arrow"></span>
                        </button>
                        <div class="challenge-invite-list" data-list="call-friends"></div>
                    </div>

                    <div class="challenge-invite-section" data-section="call-random-users">
                        <button class="challenge-invite-section__title" type="button">
                            <span>Случайные пользователи</span>
                            <span class="challenge-invite-section__arrow"></span>
                        </button>
                        <div class="challenge-invite-list" data-list="call-random-users"></div>
                    </div>

                    <div class="challenge-invite-section d-none" data-section="call-results">
                        <button class="challenge-invite-section__title" type="button">
                            <span>Результаты поиска</span>
                        </button>
                        <div class="challenge-invite-list" data-list="call-results"></div>
                    </div>
                </div>
                <p class="challenge-call-error">Выберите пользователя для вызова</p>
            </div>
        @endif

        <div
                class="challenge-invite-block"
                data-invite-url="{{route('challenges.invites.users')}}"
                data-selected-ids="{{implode(',', $selectedInviteIds)}}"
                data-original-selected-ids="{{implode(',', $selectedInviteIds)}}"
                data-mode="{{$isBattle ? 'battle' : 'challenge'}}"
        >
            <div class="challenge-invite-head d-flex ai-center gap-5 mb-8">
                <div class="challenge-invite-title d-flex gap-1">
                    <p>Приглашение</p>
                    @if($isBattle)
                        <span>к просмотру</span>
                    @endif
                </div>
                <button class="challenge-btn challenge-btn--outline add_invite" type="button">+</button>
                <div class="challenge-invite-selected-wrap">
                    <div class="challenge-invite-selected"></div>
                </div>
            </div>

            <div class="challenge-invite-dropdown">
                <input type="text" class="challenge-invite-search" placeholder="Поиск">

                <div class="challenge-invite-section" data-section="friends">
                    <button class="challenge-invite-section__title" type="button">
                        <span>Друзья</span>
                        <span class="challenge-invite-section__arrow"></span>
                    </button>
                    <div class="challenge-invite-list" data-list="friends"></div>
                </div>

                <div class="challenge-invite-section d-none" data-section="other">
                    <button class="challenge-invite-section__title" type="button">
                        <span>Другие пользователи</span>
                        <span class="challenge-invite-section__arrow"></span>
                    </button>
                    <div class="challenge-invite-list" data-list="other"></div>
                </div>

                <div class="challenge-invite-section" data-section="random">
                    <button class="challenge-invite-section__title" type="button">
                        <span>Случайные пользователи</span>
                        <span class="challenge-invite-section__arrow"></span>
                    </button>
                    <div class="challenge-invite-list" data-list="random"></div>
                </div>

                <div class="challenge-invite-section d-none" data-section="results">
                    <button class="challenge-invite-section__title" type="button">
                        <span>Результаты поиска</span>
                    </button>
                    <div class="challenge-invite-list" data-list="results"></div>
                </div>
            </div>
        </div>
    </div>

    @if(!$item || (!$item->finished && !$item->declined) || (Auth::user() && Auth::user()->is_admin()))
        <div class="d-flex ai-center gap-3 flex-wrap">
            <button class="challenge-btn challenge-btn--fill" type="submit">{{$item ? 'Изменить' : 'Создать'}}</button>
        </div>
    @endif
</form>

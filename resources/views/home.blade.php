@extends('layouts.neon.app')

@section('title')
    @if(!empty($title)){{ $title }}@endif
@endsection

@if(!empty($description))
    @push('meta-data')
        <meta name="description" content="{{ $description }}">
    @endpush
@endif

@section('content')
@php
    $heroChallenge = $topChallenges->first();
    $homeChallenges = $topChallenges->take(4);
    $homeStories = $topStories->take(3);
    $homeCampaigns = $fundedCampaigns->take(3);
@endphp

<div class="deels-source-home light_theme light_there">
    <main>
        <section class="source-hero theme-gradient">
            <div class="container source-hero-grid">
                <div class="source-hero-copy">
                    <span class="eyebrow">✦ Здесь начинается движение</span>
                    <h1>
                        Твоя идея<br>
                        может стать <em>движением</em>
                    </h1>
                    <p>
                        Создавай челленджи, снимай ответы, участвуй в баттлах, рассказывай истории и собирай поддержку на идеи, которые важны тебе.
                    </p>
                    <div class="source-hero-actions">
                        <a href="{{ route('challenges.create') }}" class="button button-primary">Создать челлендж →</a>
                        <a href="{{ route('stories.catalog') }}" class="button button-glass">▶ Смотреть ленту</a>
                    </div>
                    <div class="source-hero-proof">
                        <div class="source-avatar-stack" aria-hidden="true">
                            <span>АК</span><span>МС</span><span>ОЛ</span><span>+{{ number_format($usersCount ?? 0, 0, ',', ' ') }}</span>
                        </div>
                        <p><strong>{{ number_format($usersCount ?? 0, 0, ',', ' ') }}+</strong><br>пользователей уже в Deels</p>
                    </div>
                </div>

                <div class="source-hero-visual">
                    <div class="source-orbit source-orbit-one"></div>
                    <div class="source-orbit source-orbit-two"></div>
                    <div class="source-floating-chip source-chip-prize">
                        <span>🏆</span>
                        <strong>{{ $heroChallenge && $heroChallenge->reward_amount ? number_format($heroChallenge->reward_amount, 0, ',', ' ').' ₽' : '50 000 ₽' }}</strong>
                        <small>призовой фонд</small>
                    </div>
                    <div class="source-floating-chip source-chip-trend">
                        <span>↗</span>
                        <strong>В тренде</strong>
                        <small>{{ number_format($storiesCount ?? 0, 0, ',', ' ') }} сторис</small>
                    </div>

                    <div class="source-phone-frame">
                        <div class="source-phone-top">
                            <span class="source-brand"><span class="source-brand-mark">D</span></span>
                            <span>◌</span>
                        </div>
                        <div class="source-phone-video">
                            @if($heroChallenge && $heroChallenge->type === 'video' && $heroChallenge->video_preview)
                                <video src="{{ $heroChallenge->video_preview }}" poster="{{ $heroChallenge->thumbnail }}" muted loop autoplay playsinline style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"></video>
                            @elseif($heroChallenge && ($heroChallenge->thumbnail || $heroChallenge->path))
                                <img src="{{ $heroChallenge->thumbnail ?: $heroChallenge->path }}" alt="{{ $heroChallenge->title }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
                            @else
                                <span class="source-poster-emoji">🕺</span>
                            @endif
                            <span class="source-phone-live">DEELS • LIVE</span>
                            <div class="source-phone-side">
                                <span>♡<small>{{ number_format($heroChallenge->likes_count ?? 0, 0, ',', ' ') }}</small></span>
                                <span>◯<small>{{ number_format($heroChallenge->comments_count ?? 0, 0, ',', ' ') }}</small></span>
                                <span>↗<small>92</small></span>
                            </div>
                            <div class="source-phone-caption">
                                <small>{{ $heroChallenge && $heroChallenge->user ? '@'.$heroChallenge->user->username : '@deels' }}</small>
                                <strong>{{ $heroChallenge->title ?? 'Повтори летний движ' }}</strong>
                                <span>#челлендж #deels</span>
                            </div>
                        </div>
                        <div class="source-phone-nav"><span>⌂</span><span>⌕</span><b>+</b><span>◌</span><span>●</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="source-ecosystem" aria-labelledby="ecosystem-title">
            <div class="container">
                <div class="source-section-head" style="margin-bottom:28px">
                    <div>
                        <span class="eyebrow">✦ Больше, чем лента</span>
                        <h2 id="ecosystem-title">Всё, что можно делать в Deels</h2>
                        <p>Один профиль объединяет творчество, соревнования, поддержку авторов и общение.</p>
                    </div>
                </div>
                <div class="source-ecosystem-grid">
                    <a class="source-ecosystem-card" href="{{ route('challenges.catalog', ['content' => 'challenges']) }}"><span class="source-ecosystem-icon">✦</span><strong>Челленджи</strong><span>Запускай идеи, отвечай видео и собирай голоса.</span></a>
                    <a class="source-ecosystem-card" href="{{ route('challenges.catalog', ['content' => 'battles']) }}"><span class="source-ecosystem-icon">⚡</span><strong>Баттлы</strong><span>Вызывай соперников и соревнуйся один на один.</span></a>
                    <a class="source-ecosystem-card" href="{{ route('stories.catalog') }}"><span class="source-ecosystem-icon">▶</span><strong>Истории</strong><span>Публикуй вертикальные видео и находи аудиторию.</span></a>
                    <a class="source-ecosystem-card" href="{{ route('browse_campaign') }}"><span class="source-ecosystem-icon">💜</span><strong>Копилки</strong><span>Собирай поддержку на мечты, проекты и добрые дела.</span></a>
                    <a class="source-ecosystem-card" href="{{ Auth::check() ? route('user_wallet') : route('login') }}"><span class="source-ecosystem-icon">₽</span><strong>Кошелёк</strong><span>Пополняй баланс, получай поддержку и следи за операциями.</span></a>
                    <a class="source-ecosystem-card" href="{{ Auth::check() ? route('messages') : route('login') }}"><span class="source-ecosystem-icon">✉</span><strong>Общение</strong><span>Подписывайся, находи авторов и общайся напрямую.</span></a>
                </div>
            </div>
        </section>

        @if($homeChallenges->count())
        <section class="source-section">
            <div class="container">
                <div class="source-section-head">
                    <div>
                        <span class="eyebrow">✦ Горячее сейчас</span>
                        <h2>Челленджи, о которых говорят</h2>
                        <p>Выбирай идею, снимай свой ответ и забирай внимание аудитории.</p>
                    </div>
                    <a href="{{ route('challenges.catalog') }}" class="source-text-link">Смотреть все →</a>
                </div>
                <div class="source-horizontal-cards">
                    @foreach($homeChallenges as $challenge)
                        @php
                            $participants = $challenge->stories()->active()->count();
                            $reward = (int)($challenge->reward_amount ?? 0);
                        @endphp
                        <article class="source-video-card">
                            <a href="{{ route('challenge_page', $challenge->id) }}" class="source-poster">
                                @if($challenge->type === 'video' && $challenge->video_preview)
                                    <video src="{{ $challenge->video_preview }}" poster="{{ $challenge->thumbnail }}" muted loop autoplay playsinline></video>
                                @elseif($challenge->thumbnail || $challenge->path)
                                    <img src="{{ $challenge->thumbnail ?: $challenge->path }}" alt="{{ $challenge->title }}">
                                @else
                                    <span class="source-poster-placeholder"></span><span class="source-poster-emoji-card">✦</span>
                                @endif
                                <div class="source-poster-top">
                                    <span class="source-poster-tag">Челлендж</span>
                                    <span class="source-round-action">♡</span>
                                </div>
                                <div class="source-poster-caption">
                                    <span>{{ $challenge->user ? '@'.$challenge->user->username : '@deels' }}</span>
                                    <strong>{{ $challenge->title }}</strong>
                                </div>
                                <span class="source-play-button">▶</span>
                            </a>
                            <div class="source-card-meta">
                                <div><strong>{{ $reward > 0 ? number_format($reward, 0, ',', ' ').' ₽' : 'Без приза' }}</strong><span>призовой фонд</span></div>
                                <div><strong>{{ number_format($participants, 0, ',', ' ') }}</strong><span>участников</span></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <section class="source-live-stats" aria-labelledby="live-stats-title">
            <div class="container">
                <div class="source-live-stats-card">
                    <div class="source-live-stats-head">
                        <div><span class="eyebrow">✦ Deels прямо сейчас</span><h2 id="live-stats-title">Это уже действующая экосистема</h2></div>
                        <p>Не рекламные обещания: цифры ниже считаются из текущих данных платформы и обновляются автоматически.</p>
                    </div>
                    <div class="source-live-stats-grid">
                        <div class="source-live-stat"><strong>{{ number_format($usersCount ?? 0, 0, ',', ' ') }}</strong><span>пользователей</span></div>
                        <div class="source-live-stat"><strong>{{ number_format($storiesViewsCount ?? 0, 0, ',', ' ') }}</strong><span>просмотров контента</span></div>
                        <div class="source-live-stat"><strong>{{ number_format($campaignsCount ?? 0, 0, ',', ' ') }}</strong><span>активных копилок</span></div>
                        <div class="source-live-stat"><strong>{{ number_format($fundRaised ?? 0, 0, ',', ' ') }} ₽</strong><span>привлечено через платформу</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="source-section theme-dark-card">
            <div class="container">
                <div class="source-section-head">
                    <div>
                        <span class="eyebrow" style="color:#fff">✦ Простая механика</span>
                        <h2>От идеи до победы — три шага</h2>
                        <p style="color:rgba(255,255,255,.7)">Никаких сложных правил. Только ты, камера и желание попробовать.</p>
                    </div>
                </div>
                <div class="source-steps-grid">
                    <article><span>01</span><div class="source-step-icon">✦</div><h3>Найди свой вызов</h3><p>Выбери челлендж, который тебя цепляет.</p></article>
                    <article><span>02</span><div class="source-step-icon">▶</div><h3>Сними ответ</h3><p>Покажи свой вариант в коротком вертикальном видео.</p></article>
                    <article><span>03</span><div class="source-step-icon">🏆</div><h3>Собери голоса</h3><p>Делись, получай поддержку и выходи в топ.</p></article>
                </div>
            </div>
        </section>

        @if($homeStories->count())
        <section class="source-section">
            <div class="container source-split-feature">
                <div>
                    <div class="source-section-head" style="display:block;margin-bottom:28px">
                        <span class="eyebrow">✦ Истории Deels</span>
                        <h2>Не просто видео. Настоящие истории</h2>
                        <p>Люди рассказывают о шагах, которые изменили их жизнь. Иногда достаточно одного честного ролика, чтобы вдохновить тысячи.</p>
                    </div>
                    <a href="{{ route('stories.catalog') }}" class="button button-dark">Смотреть истории →</a>
                </div>
                <div class="source-stories-stack">
                    @foreach($homeStories as $story)
                        <a href="#story-popup" class="source-story-card show_story" data-route="{{ route('stories.preview', ['id' => $story->id, 'user_id' => Auth::id()]) }}" data-story="{{ $story->id }}" data-type="{{ $story->type }}" data-paid="{{ $story->paid }}" data-amount="{{ $story->amount }}">
                            <div class="source-story-icon">{{ ['✨','🎭','🏆'][$loop->index] ?? '✦' }}</div>
                            <div>
                                <span>{{ optional($story->created_at)->diffForHumans() }} • {{ $story->user ? '@'.$story->user->username : '@deels' }}</span>
                                <h3>{{ $story->title ?: 'История участника Deels' }}</h3>
                                <span class="source-story-more">Смотреть историю →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($homeCampaigns->count())
        <section class="source-section source-section-tint">
            <div class="container">
                <div class="source-section-head">
                    <div>
                        <span class="eyebrow">✦ Делись добром</span>
                        <h2>Копилки, которые меняют жизнь</h2>
                        <p>Поддерживай проверенные сборы и следи за результатом вместе с сообществом.</p>
                    </div>
                    <a href="{{ route('browse_campaign') }}" class="source-text-link">Смотреть все →</a>
                </div>
                <div class="source-campaign-grid">
                    @foreach($homeCampaigns as $campaign)
                        @php $raised = min(100, max(0, (int)$campaign->percent_raised())); @endphp
                        <article class="source-campaign-card">
                            <a href="{{ route('campaign_single', $campaign->slug) }}" class="source-campaign-cover">
                                @if($campaign->feature_img_url())
                                    <img src="{{ $campaign->feature_img_url()->thumbnail ?? $campaign->feature_img_url()->feature_image }}" alt="{{ $campaign->title }}">
                                @else
                                    <span>💜</span>
                                @endif
                                <span class="source-poster-tag">Проверенная копилка</span>
                            </a>
                            <div class="source-campaign-body">
                                <h3>{{ $campaign->title }}</h3>
                                <div class="source-progress-line"><span style="width:{{ $raised }}%"></span></div>
                                <div class="source-progress-meta"><strong>{{ get_amount($campaign->success_payments->sum('amount')) }}</strong><span>из {{ get_amount($campaign->goal) }}</span></div>
                                <a href="{{ route('campaign_single', $campaign->slug) }}" class="button button-soft">Поддержать</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <section class="source-economy" aria-labelledby="economy-title">
            <div class="container source-economy-grid">
                <div class="source-economy-copy">
                    <span class="eyebrow">✦ Творчество может приносить больше</span>
                    <h2 id="economy-title">Создавай. Получай поддержку. Расти.</h2>
                    <p>Deels объединяет контент и инструменты монетизации в одном профиле: автору не нужно уводить аудиторию на сторонние сервисы, чтобы получать донаты, вести копилку или контролировать баланс.</p>
                    <div class="source-economy-actions">
                        <a href="{{ route('stories.catalog') }}" class="button button-primary">Смотреть контент →</a>
                        <a href="{{ Auth::check() ? route('user_wallet') : route('register') }}" class="button button-glass">{{ Auth::check() ? 'Открыть кошелёк' : 'Создать профиль' }}</a>
                    </div>
                </div>
                <div class="source-economy-list">
                    <article class="source-economy-item"><b>♡</b><strong>Донаты авторам</strong><p>Поддержка любимого контента остаётся внутри экосистемы Deels.</p></article>
                    <article class="source-economy-item"><b>▶</b><strong>Контент с доступом</strong><p>Платные сторис и механики поддержки уже работают на существующем backend.</p></article>
                    <article class="source-economy-item"><b>₽</b><strong>Единый кошелёк</strong><p>История движений, пополнений, донатов и вывод средств в личном кабинете.</p></article>
                    <article class="source-economy-item"><b>💜</b><strong>Копилки</strong><p>Отдельный инструмент для сбора поддержки на мечты, проекты и инициативы.</p></article>
                </div>
            </div>
        </section>

        <section class="source-section">
            <div class="container source-cta-card theme-dark-card">
                <div>
                    <span class="eyebrow" style="color:#fff">✦ Твой ход</span>
                    <h2>Не просто смотри.<br>Стань частью движения.</h2>
                    <p>Создай челлендж, расскажи историю или собери поддержку на свою идею.</p>
                </div>
                <div class="source-final-actions">
                    <a href="{{ route('challenges.create') }}" class="button button-white">Создать челлендж →</a>
                    <a href="{{ route('stories.create') }}" class="button button-glass" style="color:#fff;border-color:rgba(255,255,255,.2)">Создать сторис</a>
                </div>
            </div>
        </section>
    </main>
</div>

@include('stories.modal')
@endsection
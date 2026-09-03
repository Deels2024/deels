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
    $topChallenges = $topChallenges ?? collect();
    $topStories = $topStories ?? collect();
    $fundedCampaigns = $fundedCampaigns ?? collect();
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
                    <h1>Твоя идея<br>может стать <em>движением</em></h1>
                    <p>Создавай челленджи, снимай ответы, участвуй в баттлах и поддерживай истории, которые хочется разделить.</p>
                    <div class="source-hero-actions">
                        <a href="{{ route('challenges.create') }}" class="button button-primary">Создать челлендж →</a>
                        <a href="{{ route('stories.catalog') }}" class="button button-glass">▶ Смотреть ленту</a>
                    </div>
                    <div class="source-hero-proof">
                        <div class="source-avatar-stack" aria-hidden="true">
                            <span>АК</span><span>МС</span><span>ОЛ</span><span>+{{ number_format($usersCount ?? 0, 0, ',', ' ') }}</span>
                        </div>
                        <p><strong>{{ number_format($usersCount ?? 0, 0, ',', ' ') }}+</strong><br>уже создают в Deels</p>
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
                        <small>{{ number_format($storiesCount ?? 0, 0, ',', ' ') }} ответов</small>
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
                @if($homeChallenges->count())
                    <div class="source-horizontal-cards">
                        @foreach($homeChallenges as $challenge)
                            @php
                                $participants = $challenge->stories()->active()->count();
                                $reward = (int)($challenge->reward_amount ?? 0);
                            @endphp
                            <article class="source-video-card">
                                <a href="{{ route('deels.public.challenges.show', $challenge->id) }}" class="source-poster">
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
                @else
                    <div class="empty-results" role="status"><div><h2>Новые челленджи уже готовятся</h2><p>Загляни чуть позже или создай свой — он сразу появится в каталоге после публикации.</p></div></div>
                @endif
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

        <section class="source-section">
            <div class="container source-split-feature">
                <div>
                    <div class="source-section-head" style="display:block;margin-bottom:20px">
                        <span class="eyebrow">✦ Истории Deels</span>
                        <h2>Не просто видео. Настоящие истории</h2>
                        <p>Люди рассказывают о шагах, которые изменили их жизнь. Иногда достаточно одного честного ролика, чтобы вдохновить тысячи.</p>
                    </div>
                    <div class="source-story-filter-row" aria-label="Категории историй">
                        <a href="{{ route('stories.catalog', ['type' => 'popular']) }}">Популярные</a>
                        <a href="{{ route('stories.catalog', ['type' => 'new']) }}">Новые</a>
                        <a href="{{ route('stories.catalog', ['type' => 'paid']) }}">За донаты</a>
                    </div>
                    <a href="{{ route('stories.catalog') }}" class="button button-dark">Смотреть истории →</a>
                </div>
                <div class="source-stories-stack">
                    @forelse($homeStories as $story)
                        @php
                            $isViewed = false;
                            if(Auth::check()) {
                                $isViewed = \App\Models\View::where('user_id', Auth::id())->where('story_id', $story->id)->exists();
                            }
                            $lockedStory = $story->paid && !$isViewed;
                            $previewClass = 'source-story-media '.($story->paid && $story->type !== 'video' && !$isViewed ? 'blurred_preview' : '');
                        @endphp
                        <a href="{{ route('deels.public.stories.show', $story->id) }}" class="source-story-card show_story {{ $lockedStory ? 'story_paid story__content_closed' : '' }}" data-route="{{ route('stories.preview', ['id' => $story->id, 'user_id' => Auth::id()]) }}" data-story="{{ $story->id }}" data-type="{{ $story->type }}" data-paid="{{ $story->paid }}" data-amount="{{ $story->amount }}" aria-label="Смотреть историю {{ $story->title ?: 'участника Deels' }}">
                            @include('stories.parts.preview', [
                                'story' => $story,
                                'class' => $previewClass,
                                'alt' => $story->title ?: 'История участника Deels',
                            ])
                            <div class="source-story-copy">
                                <span>{{ optional($story->created_at)->diffForHumans() }} • {{ $story->user ? '@'.$story->user->username : '@deels' }}</span>
                                <h3>{{ $story->title ?: 'История участника Deels' }}</h3>
                                <span class="source-story-more">{{ $lockedStory ? 'Открыть историю →' : 'Смотреть историю →' }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-results" role="status"><div><h2>Истории появятся здесь</h2><p>Сейчас нет доступных публикаций. Каталог обновится автоматически, когда появятся новые истории.</p></div></div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="source-section source-section-tint">
            <div class="container">
                <div class="source-section-head">
                    <div>
                        <span class="eyebrow">✦ Делись добром</span>
                        <h2>Копилки, которые меняют жизнь</h2>
                        <p>Поддерживай проверенные сборы и следи за результатом вместе с сообществом.</p>
                    </div>
                    <a href="{{ route('deels.public.campaigns.index') }}" class="source-text-link">Смотреть все →</a>
                </div>
                @if($homeCampaigns->count())
                    <div class="source-campaign-grid">
                        @foreach($homeCampaigns as $campaign)
                            @php $raised = min(100, max(0, (int)$campaign->percent_raised())); @endphp
                            <article class="source-campaign-card">
                                <a href="{{ route('deels.public.campaigns.show', $campaign->slug) }}" class="source-campaign-cover">
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
                                    <a href="{{ route('deels.public.campaigns.show', $campaign->slug) }}" class="button button-soft">Поддержать</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-results" role="status"><div><h2>Новых копилок пока нет</h2><p>Проверенные сборы появятся здесь сразу после публикации.</p></div></div>
                @endif
            </div>
        </section>

        <section class="source-section">
            <div class="container source-cta-card theme-dark-card">
                <div>
                    <span class="eyebrow" style="color:#fff">✦ Твой ход</span>
                    <h2>Готов создать то,<br>что подхватят другие?</h2>
                    <p>Начни с первого челленджа. Это займёт меньше пяти минут.</p>
                </div>
                <a href="{{ route('challenges.create') }}" class="button button-white">Создать в Deels →</a>
            </div>
        </section>
    </main>
</div>

@include('stories.modal')
@endsection

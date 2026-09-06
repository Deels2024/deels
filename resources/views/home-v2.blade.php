@extends('layouts.neon.app')

@section('body-class', 'deels-studio-enabled')

@section('title', $title ?? 'Deels — платформа челленджей, батлов и творчества')

@push('meta-data')
    <meta name="description" content="{{ $description ?? 'Участвуйте в челленджах и батлах, публикуйте видео, голосуйте и получайте награды на Deels.' }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Deels — платформа челленджей, батлов и творчества' }}">
    <meta property="og:description" content="{{ $description ?? 'Челленджи, батлы, сторис, донаты и копилки в одном сообществе.' }}">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:site_name" content="DEELS">
    <meta property="og:image" content="{{ url('/images/promo/banner1.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="robots" content="{{ !empty($homeV2Preview) ? 'noindex,nofollow,noarchive' : 'index,follow,max-image-preview:large' }}">
    <link rel="canonical" href="{{ route('home') }}">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Deels',
            'url' => route('home'),
            'description' => $description ?? 'Платформа челленджей, батлов и творчества',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/search') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('page-css')
    <link rel="stylesheet" href="{{ ext_asset('/dist/css/deels-studio.css') }}">
    <link rel="stylesheet" href="{{ ext_asset('/dist/css/home-v2.css') }}">
@endsection

@php
    $homeV2TopChallenges = collect($topChallenges ?? [])->take(8);
    $homeV2TopStories = collect($topStories ?? [])->take(10);
    $homeV2DonateStories = collect($donateStories ?? [])->take(10);
    $homeV2NewStories = collect($newStories ?? [])->take(10);
    $homeV2Battles = collect($topBattles ?? [])->take(6);
    $homeV2Directions = collect($popularDirections ?? [])->take(6);
    $homeV2LatestCampaigns = collect($latestFundedCampaigns ?? [])->take(8);
    $homeV2NewCampaigns = collect($newCampaigns ?? [])->take(8);

    $homeV2HeroChallenge = $homeV2TopChallenges->first();
    $homeV2HeroCard = $homeV2HeroChallenge
        ? (new \App\Http\Resources\Home\ChallengeCardResource($homeV2HeroChallenge))->resolve(request())
        : null;
    $homeV2HeroMedia = $homeV2HeroCard['media'] ?? [
        'type' => 'image',
        'url' => url('/home-media/video-boy.webp'),
        'poster' => url('/home-media/video-boy.webp'),
        'aspect_ratio' => '9:16',
    ];
    $homeV2HeroUrl = $homeV2HeroCard['url'] ?? route('challenges.catalog');
    $homeV2HeroTitle = $homeV2HeroCard['title'] ?? 'Покажи, что умеешь';
    $homeV2HeroMeta = !empty($homeV2HeroCard['reward_amount'])
        ? number_format((int) $homeV2HeroCard['reward_amount'], 0, ',', ' ') . ' DEELS'
        : 'Смотри задание и условия';
@endphp

@section('content')
<main class="home-v2" data-home-v2 data-bank-url="{{ route('coins_bank') }}">

    @if(!empty($homeV2Preview))
        <div class="hv2-preview" role="status">
            <strong>Предпросмотр Home v2</strong>
            <span>Эту версию видит только администратор. Публичная главная не переключена.</span>
            <a href="{{ route('home') }}">Вернуться на текущую главную</a>
        </div>
    @endif

    <section class="hv2-shell hv2-hero" aria-labelledby="home-v2-title">
        <div class="hv2-hero__copy">
            <div class="hv2-kicker">Челленджи · Баттлы · Сообщество</div>
            <h1 id="home-v2-title">Бросай вызов.<br><span>Показывай себя.</span></h1>
            <p>Участвуй в челленджах, соревнуйся в баттлах и получай награды DEELS. Здесь ценят то, что ты делаешь.</p>
            <div class="hv2-actions">
                <a class="hv2-btn hv2-btn--primary" href="#home-v2-challenges">Выбрать челлендж <span aria-hidden="true">→</span></a>
                <a class="hv2-btn hv2-btn--link" href="#how-it-works">Как это работает</a>
            </div>
        </div>
        @if($homeV2HeroCard)
            <a class="hv2-feature" href="{{ $homeV2HeroUrl }}" aria-label="Открыть челлендж «{{ $homeV2HeroTitle }}»">
                <span class="hv2-feature__media hv2-ratio hv2-ratio--vertical">
                    @if(($homeV2HeroMedia['type'] ?? 'image') === 'video')
                        <video src="{{ $homeV2HeroMedia['url'] }}" poster="{{ $homeV2HeroMedia['poster'] ?? '' }}" muted playsinline loop preload="none" aria-label="{{ $homeV2HeroTitle }}"></video>
                    @else
                        <img src="{{ $homeV2HeroMedia['url'] }}" width="450" height="800" fetchpriority="high" alt="Челлендж «{{ $homeV2HeroTitle }}»">
                    @endif
                </span>
                <span class="hv2-feature__copy">
                    <span class="hv2-feature__label">Челлендж в фокусе</span>
                    <strong>{{ \Illuminate\Support\Str::limit($homeV2HeroTitle, 58) }}</strong>
                    <span class="hv2-feature__reward">{{ $homeV2HeroMeta }}</span>
                    <span class="hv2-feature__link">Смотреть задание <b aria-hidden="true">→</b></span>
                </span>
            </a>
        @else
            <div class="hv2-hero__empty"><span>Начни с идеи.</span><p>Первый шаг — выбрать то, что тебе интересно.</p><a href="{{ route('stories.catalog') }}">Вдохновиться историями →</a></div>
        @endif
    </section>

    <section class="hv2-shell hv2-section hv2-challenges hv2-panel" aria-labelledby="home-v2-challenges">
            <div class="hv2-section-head">
                <div>
                    <h2 id="home-v2-challenges">Найди свой челлендж</h2>
                    <p class="hv2-section-description">Выбери задание и присоединяйся.</p>
                </div>
                <div class="hv2-section-head__actions">
                    @if($homeV2TopChallenges->isNotEmpty())
                        <div class="hv2-rail-controls" aria-label="Прокрутка челленджей">
                            <button type="button" data-rail-prev aria-label="Предыдущие челленджи">←</button>
                            <button type="button" data-rail-next aria-label="Следующие челленджи">→</button>
                        </div>
                    @endif
                    <a href="{{ route('challenges.catalog') }}" aria-label="Все челленджи"><span class="hv2-link-full">Все челленджи</span><span class="hv2-link-compact" aria-hidden="true">Все</span><span aria-hidden="true">→</span></a>
                </div>
            </div>
            @if($homeV2TopChallenges->isNotEmpty())
                <div class="hv2-filters" data-challenge-filters aria-label="Фильтры челленджей">
                    <button type="button" class="is-active" data-filter="all" aria-pressed="true">Все</button>
                    <button type="button" data-filter="reward" aria-pressed="false">С призом</button>
                    <button type="button" data-filter="new" aria-pressed="false">Новые</button>
                    <button type="button" data-filter="ending" aria-pressed="false">Завершаются</button>
                </div>
                <div class="hv2-rail hv2-rail--challenges" data-rail>
                @foreach($homeV2TopChallenges as $challenge)
                    @php
                        $challengeCard = (new \App\Http\Resources\Home\ChallengeCardResource($challenge))->resolve(request());
                        $challengeMedia = $challengeCard['media'];
                        $challengeDeadline = !empty($challengeCard['deadline']) ? \Carbon\Carbon::parse($challengeCard['deadline']) : null;
                        $isNewChallenge = $challenge->created_at && $challenge->created_at->greaterThanOrEqualTo(now()->subDays(14));
                        $isEndingChallenge = $challengeDeadline && $challengeDeadline->isFuture() && $challengeDeadline->lessThanOrEqualTo(now()->addDays(7));
                    @endphp
                    <article class="hv2-challenge-card" data-challenge-card data-has-reward="{{ $challengeCard['reward_amount'] > 0 ? '1' : '0' }}" data-is-new="{{ $isNewChallenge ? '1' : '0' }}" data-is-ending="{{ $isEndingChallenge ? '1' : '0' }}">
                        <a class="hv2-challenge-card__media hv2-ratio hv2-ratio--vertical" href="{{ $challengeCard['url'] }}">
                            @if(($challengeMedia['type'] ?? 'image') === 'video')
                                <video src="{{ $challengeMedia['url'] }}" poster="{{ $challengeMedia['poster'] ?? '' }}" muted playsinline preload="none" aria-label="{{ $challengeCard['title'] }}"></video>
                            @else
                                <img src="{{ $challengeMedia['url'] ?? '/uploads/placeholder-image.png' }}" width="360" height="640" loading="lazy" alt="Челлендж «{{ $challengeCard['title'] }}»">
                            @endif
                            <span class="hv2-status {{ !empty($challengeCard['status']['finished']) ? 'hv2-status--finished' : '' }}"><i aria-hidden="true"></i>{{ $challengeCard['status']['title'] }}</span>
                            @if($challengeCard['reward_amount'] > 0)
                                <span class="hv2-media-reward">{{ number_format($challengeCard['reward_amount'], 0, ',', ' ') }} <small>DEELS</small></span>
                            @endif
                            @if(($challengeMedia['type'] ?? 'image') === 'video')<span class="hv2-play" aria-hidden="true">▶</span>@endif
                        </a>
                        <div class="hv2-challenge-card__body">
                            <h3><a href="{{ $challengeCard['url'] }}">{{ \Illuminate\Support\Str::limit($challengeCard['title'], 54) }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($challengeCard['description']), 72) }}</p>
                            <div class="hv2-card-meta">
                                <span>{{ number_format($challengeCard['participants']['current'], 0, ',', ' ') }} участников</span>
                                @if($challengeDeadline)<time datetime="{{ $challengeDeadline->toDateString() }}">до {{ $challengeDeadline->format('d.m') }}</time>@endif
                            </div>
                            <a class="hv2-card-action" href="{{ $challengeCard['url'] }}">{{ !empty($challengeCard['status']['finished']) ? 'Смотреть итоги' : 'Открыть челлендж' }} <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
                </div>
                <p class="hv2-empty-filter" data-filter-empty hidden>В этой подборке пока нет челленджей. Посмотрите все актуальные челленджи.</p>
            @else
                <div class="hv2-content-empty">
                    <span aria-hidden="true">✦</span>
                    <div><strong>Новые челленджи уже готовятся</strong><p>Загляните в каталог или запустите собственную идею.</p></div>
                    <a class="hv2-card-action" href="{{ route('challenges.create') }}">Создать челлендж</a>
                </div>
            @endif
    </section>

    @if($homeV2Battles->isNotEmpty())
        <section class="hv2-shell hv2-section hv2-battles hv2-panel" aria-labelledby="home-v2-battles">
            <div class="hv2-section-head">
                <div><h2 id="home-v2-battles">Баттлы</h2><p class="hv2-section-description">Соревнуйся и поддерживай участников.</p></div>
                <div class="hv2-section-head__actions">
                    <div class="hv2-rail-controls" aria-label="Прокрутка баттлов"><button type="button" data-rail-prev aria-label="Назад">←</button><button type="button" data-rail-next aria-label="Вперёд">→</button></div>
                    <a href="{{ route('deels.public.battles.index') }}" aria-label="Все баттлы"><span class="hv2-link-full">Все баттлы</span><span class="hv2-link-compact" aria-hidden="true">Все</span><span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="hv2-rail hv2-rail--battles" data-rail>
                @foreach($homeV2Battles as $battle)
                    @php
                        $battleCard = (new \App\Http\Resources\Home\BattleCardResource($battle))->resolve(request());
                        $battleDeadline = !empty($battleCard['deadline']) ? \Carbon\Carbon::parse($battleCard['deadline']) : null;
                    @endphp
                    <article class="hv2-battle-card">
                        <a class="hv2-battle-card__pair" href="{{ $battleCard['url'] }}">
                            @foreach($battleCard['opponents'] as $opponent)
                                <span class="hv2-battle-card__media hv2-ratio hv2-ratio--vertical">
                                    @if(!empty($opponent['media']))
                                        @if(($opponent['media']['type'] ?? 'image') === 'video')
                                            <video src="{{ $opponent['media']['url'] }}" poster="{{ $opponent['media']['poster'] ?? '' }}" muted playsinline preload="none" aria-label="Участник баттла"></video>
                                        @else
                                            <img src="{{ $opponent['media']['url'] ?? '/uploads/placeholder-image.png' }}" width="360" height="640" loading="lazy" alt="Участник баттла">
                                        @endif
                                    @else
                                        <span class="hv2-battle-card__waiting">
                                            @if(!empty($opponent['author']['avatar']))<img src="{{ $opponent['author']['avatar'] }}" width="72" height="72" alt="">@endif
                                            <small>Ждём соперника</small>
                                        </span>
                                    @endif
                                </span>
                            @endforeach
                            <span class="hv2-vs">VS</span>
                        </a>
                        <h3><a href="{{ $battleCard['url'] }}">{{ \Illuminate\Support\Str::limit($battleCard['title'], 60) }}</a></h3>
                        <div class="hv2-battle-card__meta">
                            <strong>{{ $battleCard['reward_amount'] > 0 ? number_format($battleCard['reward_amount'], 0, ',', ' ') . ' DEELS' : 'Баттл сообщества' }}</strong>
                            <span>{{ number_format($battleCard['stats']['likes'], 0, ',', ' ') }} голосов</span>
                            @if($battleDeadline)<time datetime="{{ $battleDeadline->toDateString() }}">до {{ $battleDeadline->format('d.m') }}</time>@endif
                        </div>
                        <a class="hv2-card-action" href="{{ $battleCard['url'] }}">Смотреть баттл <span aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $homeV2StorySections = [
            ['id' => 'top', 'title' => 'Популярное', 'items' => $homeV2TopStories, 'url' => route('stories.catalog', ['type' => 'popular']), 'donate' => false],
            ['id' => 'donate', 'title' => 'За донаты', 'items' => $homeV2DonateStories, 'url' => route('stories.catalog', ['type' => 'paid']), 'donate' => true],
            ['id' => 'new', 'title' => 'Новинки', 'items' => $homeV2NewStories, 'url' => route('stories.catalog', ['type' => 'new']), 'donate' => false],
        ];
        $homeV2HasStories = $homeV2TopStories->isNotEmpty()
            || $homeV2DonateStories->isNotEmpty()
            || $homeV2NewStories->isNotEmpty();
    @endphp

    <section class="hv2-shell hv2-section hv2-collection hv2-panel" data-home-tabs>
        <div class="hv2-section-head">
            <div class="hv2-collection-heading"><h2>Истории сообщества</h2><p class="hv2-section-description">Смотри, вдохновляйся, делись своим.</p></div>
            <div class="hv2-section-head__actions"><a data-collection-catalog href="{{ route('stories.catalog') }}" aria-label="Все истории"><span class="hv2-link-full">Все истории</span><span class="hv2-link-compact" aria-hidden="true">Все</span><span aria-hidden="true">→</span></a></div>
        </div>
        <div class="hv2-collection-toolbar">
            <div class="hv2-tabs" aria-label="Подборки историй">
                @foreach($homeV2StorySections as $storySection)
                    @if($storySection['items']->isNotEmpty())
                        <button type="button" data-home-tab="{{ $storySection['id'] }}" data-catalog-url="{{ $storySection['url'] }}" aria-pressed="false">{{ $storySection['title'] }}</button>
                    @endif
                @endforeach
            </div>
            <div class="hv2-rail-controls" aria-label="Прокрутка историй"><button type="button" data-rail-prev aria-label="Предыдущие истории">←</button><button type="button" data-rail-next aria-label="Следующие истории">→</button></div>
        </div>
    @foreach($homeV2StorySections as $storySection)
        @if($storySection['items']->isNotEmpty())
            <section class="hv2-collection-panel hv2-stories" data-home-panel="{{ $storySection['id'] }}" aria-labelledby="home-v2-stories-{{ $storySection['id'] }}">
                <h3 class="hv2-panel-title" id="home-v2-stories-{{ $storySection['id'] }}">{{ $storySection['title'] }}</h3>
                <div class="hv2-rail hv2-rail--stories" data-rail>
                    @foreach($storySection['items'] as $story)
                        @php
                            $storyCard = (new \App\Http\Resources\Home\StoryCardResource($story))->resolve(request());
                            $storyMedia = $storyCard['media'];
                            $storyLocked = $storyCard['access']['paid'] && !$storyCard['access']['viewed'];
                        @endphp
                        <a href="{{ route('deels.public.stories.show', $story->id) }}"
                           class="hv2-story-card show_story {{ $storyLocked ? 'story_paid story__content_closed' : '' }}"
                           data-route="{{ route('stories.preview', ['id' => $story->id, 'user_id' => Auth::id()]) }}"
                           data-story="{{ $story->id }}"
                           data-type="{{ $story->type }}"
                           data-paid="{{ $storyCard['access']['paid'] ? 1 : 0 }}"
                           data-amount="{{ $storyCard['access']['amount'] }}">
                            <span class="hv2-story-card__media hv2-ratio hv2-ratio--vertical">
                                @if(($storyMedia['type'] ?? 'image') === 'video')
                                    <video src="{{ $storyMedia['url'] }}" poster="{{ $storyMedia['poster'] ?? '' }}" muted playsinline preload="none" aria-label="{{ $storyCard['title'] ?: 'Сторис Deels' }}"></video>
                                @else
                                    <img src="{{ $storyMedia['url'] ?? '/uploads/placeholder-image.png' }}" width="360" height="640" loading="lazy" alt="{{ $storyCard['title'] ?: 'Сторис Deels' }}">
                                @endif
                                @if($storyLocked)
                                    <span class="hv2-lock" aria-label="Платная сторис">⌑</span>
                                    <span class="hv2-price">{{ number_format($storyCard['access']['amount'], 0, ',', ' ') }} DEELS</span>
                                @else
                                    <span class="hv2-play" aria-hidden="true">▶</span>
                                @endif
                            </span>
                            <span class="hv2-story-card__user">
                                <span>
                                    @if(!empty($storyCard['author']['avatar']))<img src="{{ $storyCard['author']['avatar'] }}" width="28" height="28" loading="lazy" alt="">@else<span class="hv2-avatar" aria-hidden="true"></span>@endif
                                    {{ $storyCard['author']['username'] ?? $storyCard['author']['name'] ?? 'DEELS' }}
                                </span>
                                <small>{{ number_format($storyCard['stats']['views'], 0, ',', ' ') }}</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if(!$homeV2HasStories)
        <section class="hv2-collection-panel hv2-stories hv2-panel" aria-labelledby="home-v2-stories-empty">
            <div class="hv2-section-head"><h2 id="home-v2-stories-empty">Сторис сообщества</h2></div>
            <div class="hv2-content-empty">
                <span aria-hidden="true">▷</span>
                <div><strong>Здесь скоро появятся новые истории</strong><p>Станьте первым автором в сегодняшней ленте.</p></div>
                <a class="hv2-card-action" href="{{ route('stories.catalog') }}">Открыть сторис</a>
            </div>
        </section>
    @endif

    </section>

    @php
        $homeV2CampaignSections = [
            ['id' => 'latest', 'title' => 'Недавно поддержали', 'items' => $homeV2LatestCampaigns, 'url' => route('deels.public.campaigns.index')],
            ['id' => 'new', 'title' => 'Новые', 'items' => $homeV2NewCampaigns, 'url' => route('deels.public.campaigns.index', ['type' => 'new'])],
        ];
    @endphp

    <section class="hv2-shell hv2-section hv2-collection hv2-panel" data-home-tabs>
        <div class="hv2-section-head">
            <div class="hv2-collection-heading"><h2>Копилки</h2><p class="hv2-section-description">Помоги хорошей идее стать реальностью.</p></div>
            <div class="hv2-section-head__actions"><a data-collection-catalog href="{{ route('deels.public.campaigns.index') }}" aria-label="Все копилки"><span class="hv2-link-full">Все копилки</span><span class="hv2-link-compact" aria-hidden="true">Все</span><span aria-hidden="true">→</span></a></div>
        </div>
        <div class="hv2-collection-toolbar">
        <div class="hv2-tabs" aria-label="Подборки копилок">
            @foreach($homeV2CampaignSections as $campaignSection)
                @if($campaignSection['items']->isNotEmpty())
                    <button type="button" data-home-tab="{{ $campaignSection['id'] }}" data-catalog-url="{{ $campaignSection['url'] }}" aria-pressed="false">{{ $campaignSection['title'] }}</button>
                @endif
            @endforeach
        </div>
            <div class="hv2-rail-controls" aria-label="Прокрутка копилок"><button type="button" data-rail-prev aria-label="Предыдущие копилки">←</button><button type="button" data-rail-next aria-label="Следующие копилки">→</button></div>
        </div>
    @foreach($homeV2CampaignSections as $campaignSection)
        @if($campaignSection['items']->isNotEmpty())
            <section class="hv2-collection-panel hv2-funds" data-home-panel="{{ $campaignSection['id'] }}" aria-labelledby="home-v2-funds-{{ $campaignSection['id'] }}">
                <h3 class="hv2-panel-title" id="home-v2-funds-{{ $campaignSection['id'] }}">{{ $campaignSection['title'] }}</h3>
                <div class="hv2-rail hv2-rail--funds" data-rail>
                    @foreach($campaignSection['items'] as $campaign)
                        @php
                            $campaignCard = (new \App\Http\Resources\Home\CampaignCardResource($campaign))->resolve(request());
                            $campaignMedia = $campaignCard['media'];
                            $progress = (float) $campaignCard['funding']['progress'];
                        @endphp
                        <a class="hv2-fund-card" href="{{ $campaignCard['url'] }}">
                            <span class="hv2-fund-card__media hv2-ratio hv2-ratio--vertical">
                                @if(($campaignMedia['type'] ?? 'image') === 'video')
                                    <video src="{{ $campaignMedia['url'] }}" poster="{{ $campaignMedia['poster'] ?? '' }}" muted playsinline preload="none" aria-label="{{ $campaignCard['title'] }}"></video>
                                    <span class="hv2-play" aria-hidden="true">▶</span>
                                @else
                                    <img src="{{ $campaignMedia['url'] ?? '/uploads/placeholder-image.png' }}" width="360" height="640" loading="lazy" alt="Копилка «{{ $campaignCard['title'] }}»">
                                @endif
                            </span>
                            <span class="hv2-fund-card__body">
                                <strong>{{ \Illuminate\Support\Str::limit($campaignCard['title'], 48) }}</strong>
                                <span class="hv2-progress" aria-label="Собрано {{ round($progress) }} процентов"><i style="width: {{ min(100, max(0, $progress)) }}%"></i></span>
                                <span class="hv2-fund-card__row"><b>{{ round($progress) }}%</b><small>{{ number_format((float) $campaignCard['funding']['raised'], 0, ',', ' ') }} из {{ number_format((float) $campaignCard['funding']['goal'], 0, ',', ' ') }} ₽</small></span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if($homeV2LatestCampaigns->isEmpty() && $homeV2NewCampaigns->isEmpty())
        <div class="hv2-content-empty"><div><strong>У каждой идеи есть начало</strong><p>Расскажи о своей цели и открой копилку.</p></div><a class="hv2-card-action" href="{{ route('start_campaign') }}">Создать копилку</a></div>
    @endif
    @if($homeV2Directions->isNotEmpty())
        <nav class="hv2-directions" aria-label="Направления копилок">
                @foreach($homeV2Directions as $direction)
                    <a class="hv2-direction" href="{{ route('campaigns.category', $direction->slug) }}">
                        <strong>{{ $direction->category_name }}</strong>
                        <small>{{ number_format((int) ($direction->campaigns_count ?? 0), 0, ',', ' ') }} проектов</small>
                    </a>
                @endforeach
        </nav>
    @endif

    </section>

    <section class="hv2-shell hv2-bank" aria-labelledby="home-v2-bank-title">
        <div class="hv2-bank__copy"><span class="hv2-section-kicker">Поддержка внутри сообщества</span><h2 id="home-v2-bank-title">Банк DEELS</h2><p>DEELS — внутренняя единица платформы. Условия наград и участия указаны в каждом челлендже.</p></div>
        <div class="hv2-bank__value">
            <strong data-bank-counter aria-label="{{ number_format((int) ($bank ?? 0), 0, ',', ' ') }} DEELS">{{ number_format((int) ($bank ?? 0), 0, ',', ' ') }}</strong><span>DEELS в банке</span>
            <small data-bank-status>Последнее значение</small><button type="button" data-bank-retry hidden>Обновить</button>
        </div>
        <div class="hv2-bank__stats"><div><strong>{{ number_format((int) ($challengesCount ?? 0), 0, ',', ' ') }}</strong><span>активных челленджей</span></div><div><strong>{{ number_format((int) ($usersCount ?? 0), 0, ',', ' ') }}</strong><span>пользователей Deels</span></div></div>
    </section>

    <section id="how-it-works" class="hv2-shell hv2-steps hv2-panel" aria-labelledby="home-v2-how">
        <h2 id="home-v2-how">Как участвовать</h2>
        <div class="hv2-step"><span>01</span><div><strong>Найди свой вызов</strong><p>Посмотри задание и условия участия.</p></div></div>
        <div class="hv2-step"><span>02</span><div><strong>Покажи результат</strong><p>Добавь видео или отметку по правилам челленджа.</p></div></div>
        <div class="hv2-step"><span>03</span><div><strong>Будь частью движения</strong><p>Следи за участниками и поддерживай авторов.</p></div></div>
    </section>

    <section class="hv2-shell hv2-cta" aria-labelledby="home-v2-cta">
        <div><span class="hv2-section-kicker">Хорошая идея — только начало</span><h2 id="home-v2-cta">Есть идея? Дай ей старт.</h2><p>Создай свой челлендж или расскажи историю. Сообщество рядом.</p></div>
        <div class="hv2-actions"><a class="hv2-btn hv2-btn--primary" href="{{ route('challenges.create') }}">Создать челлендж <span aria-hidden="true">+</span></a><a class="hv2-btn hv2-btn--link" href="{{ route('stories.create') }}">Добавить историю</a></div>
    </section>

    @include('stories.modal')
</main>
@endsection

@section('page-js')
    <script src="{{ ext_asset('/dist/js/home-v2.js') }}" defer></script>
@endsection

@extends('layouts.neon.app')

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
    <link rel="stylesheet" href="{{ ext_asset('/dist/css/home-v2.css') }}">
@endsection

@php
    $homeV2BankDigits = str_split(str_pad((string) max(0, (int) ($bank ?? 0)), 8, '0', STR_PAD_LEFT));
    $homeV2TopChallenges = collect($topChallenges ?? [])->take(8);
    $homeV2TopStories = collect($topStories ?? [])->take(10);
    $homeV2DonateStories = collect($donateStories ?? [])->take(10);
    $homeV2NewStories = collect($newStories ?? [])->take(10);
    $homeV2Battles = collect($topBattles ?? [])->take(6);
    $homeV2Directions = collect($popularDirections ?? [])->take(6);
    $homeV2LatestCampaigns = collect($latestFundedCampaigns ?? [])->take(8);
    $homeV2NewCampaigns = collect($newCampaigns ?? [])->take(8);
    $homeV2DirectionIcons = ['✦', '◉', '➜', '◇', '⌁', '♡'];
@endphp

@section('content')
<main class="home-v2" data-home-v2 data-bank-url="{{ route('coins_bank') }}">
    <div class="home-v2__ambient" aria-hidden="true"></div>

    @if(!empty($homeV2Preview))
        <div class="hv2-preview" role="status">
            <strong>Предпросмотр Home v2</strong>
            <span>Эту версию видит только администратор. Публичная главная не переключена.</span>
            <a href="{{ route('home') }}">Вернуться на текущую главную</a>
        </div>
    @endif

    <section class="hv2-shell hv2-hero hv2-panel" aria-labelledby="home-v2-title">
        <div class="hv2-hero__copy">
            <div class="hv2-kicker">DEELS</div>
            <h1 id="home-v2-title">Платформа челленджей,<br>батлов и творчества</h1>
            <p>Участвуй в челленджах, снимай видео, собирай голоса и получай награды.</p>
            <div class="hv2-actions">
                <a class="hv2-btn hv2-btn--primary" href="{{ route('challenges.catalog') }}">Выбрать челлендж</a>
                <a class="hv2-btn" href="{{ route('challenges.create') }}">Создать челлендж</a>
                <a class="hv2-btn hv2-btn--link" href="#how-it-works">Как это работает <span aria-hidden="true">→</span></a>
            </div>
            <div class="hv2-stores" aria-label="Скачать приложение Deels">
                <a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank" rel="noopener">
                    <img src="/images/promo/android.png" width="160" height="48" alt="Скачать Deels в Google Play">
                </a>
                <a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank" rel="noopener">
                    <img src="/images/promo/appstore.png" width="160" height="48" alt="Скачать Deels в App Store">
                </a>
            </div>
        </div>
        <div class="hv2-hero__art" aria-hidden="true">
            <img src="/dist/images/home-v2-hero.png" width="640" height="640" fetchpriority="high" alt="">
        </div>
    </section>

    <section class="hv2-shell hv2-bank hv2-panel" aria-label="Банк и показатели Deels">
        <div class="hv2-bank__counter">
            <h2>БАНК <span>DEELS</span></h2>
            <div class="hv2-counter" data-bank-counter aria-label="{{ number_format((int) ($bank ?? 0), 0, ',', ' ') }} DEELS">
                @foreach($homeV2BankDigits as $digit)
                    <span class="hv2-counter__digit">{{ $digit }}</span>
                @endforeach
            </div>
        </div>
        <div class="hv2-metrics">
            <div class="hv2-metric"><span class="hv2-metric__icon" aria-hidden="true">✦</span><strong>{{ number_format((int) ($challengesCount ?? 0), 0, ',', ' ') }}</strong><span>активных<br>челленджей</span></div>
            <div class="hv2-metric"><span class="hv2-metric__icon" aria-hidden="true">◉</span><strong>{{ number_format((int) ($participantsCount ?? $usersCount ?? 0), 0, ',', ' ') }}</strong><span>участников</span></div>
            <div class="hv2-metric"><span class="hv2-metric__icon" aria-hidden="true">▷</span><strong>{{ number_format((int) ($storiesCount ?? 0), 0, ',', ' ') }}</strong><span>видео и сторис</span></div>
            <div class="hv2-metric"><span class="hv2-metric__icon" aria-hidden="true">◇</span><strong>{{ number_format((int) ($rewardsTotal ?? 0), 0, ',', ' ') }}</strong><span>DEELS наград</span></div>
        </div>
    </section>

    @if($homeV2TopChallenges->isNotEmpty())
        <section class="hv2-shell hv2-section hv2-challenges hv2-panel" aria-labelledby="home-v2-challenges">
            <div class="hv2-section-head">
                <div>
                    <span class="hv2-section-kicker">Главное на Deels</span>
                    <h2 id="home-v2-challenges">Актуальные челленджи</h2>
                </div>
                <div class="hv2-section-head__actions">
                    <div class="hv2-rail-controls" aria-label="Прокрутка челленджей">
                        <button type="button" data-rail-prev aria-label="Предыдущие челленджи">←</button>
                        <button type="button" data-rail-next aria-label="Следующие челленджи">→</button>
                    </div>
                    <a href="{{ route('challenges.catalog') }}">Смотреть все <span aria-hidden="true">→</span></a>
                </div>
            </div>
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
                        <a class="hv2-challenge-card__media hv2-ratio hv2-ratio--wide" href="{{ $challengeCard['url'] }}">
                            @if(($challengeMedia['type'] ?? 'image') === 'video')
                                <video src="{{ $challengeMedia['url'] }}" poster="{{ $challengeMedia['poster'] ?? '' }}" muted playsinline preload="none" aria-label="{{ $challengeCard['title'] }}"></video>
                            @else
                                <img src="{{ $challengeMedia['url'] ?? '/uploads/placeholder-image.png' }}" width="640" height="360" loading="lazy" alt="Челлендж «{{ $challengeCard['title'] }}»">
                            @endif
                            <span class="hv2-status"><i aria-hidden="true"></i>{{ $challengeCard['status']['title'] }}</span>
                        </a>
                        <div class="hv2-challenge-card__body">
                            <h3><a href="{{ $challengeCard['url'] }}">{{ \Illuminate\Support\Str::limit($challengeCard['title'], 54) }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($challengeCard['description']), 72) }}</p>
                            <div class="hv2-prize">
                                @if($challengeCard['reward_amount'] > 0)
                                    {{ number_format($challengeCard['reward_amount'], 0, ',', ' ') }} DEELS
                                @else
                                    Участие открыто
                                @endif
                            </div>
                            <div class="hv2-card-meta">
                                <span>{{ number_format($challengeCard['participants']['current'], 0, ',', ' ') }} участников</span>
                                @if($challengeDeadline)<time datetime="{{ $challengeDeadline->toDateString() }}">до {{ $challengeDeadline->format('d.m') }}</time>@endif
                            </div>
                            <a class="hv2-card-action" href="{{ $challengeCard['url'] }}">Участвовать</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="hv2-empty-filter" data-filter-empty hidden>В этой подборке пока нет челленджей. Посмотрите все актуальные челленджи.</p>
        </section>

        <section id="how-it-works" class="hv2-shell hv2-steps hv2-panel" aria-labelledby="home-v2-how">
            <h2 id="home-v2-how">Как участвовать</h2>
            <div class="hv2-step"><span>1</span><p>Выбери челлендж</p></div>
            <div class="hv2-step"><span>2</span><p>Сними видео</p></div>
            <div class="hv2-step"><span>3</span><p>Получи голоса и награду</p></div>
        </section>
    @endif

    @php
        $homeV2StorySections = [
            ['id' => 'top', 'title' => 'Топ сторис', 'items' => $homeV2TopStories, 'url' => route('stories.catalog', ['type' => 'popular']), 'donate' => false],
            ['id' => 'donate', 'title' => 'Сторис за донаты', 'items' => $homeV2DonateStories, 'url' => route('stories.catalog', ['type' => 'paid']), 'donate' => true],
            ['id' => 'new', 'title' => 'Новинки', 'items' => $homeV2NewStories, 'url' => route('stories.catalog', ['type' => 'new']), 'donate' => false],
        ];
    @endphp

    @foreach($homeV2StorySections as $storySection)
        @if($storySection['items']->isNotEmpty())
            <section class="hv2-shell hv2-section hv2-stories hv2-panel {{ $storySection['id'] !== 'top' ? 'hv2-stories--four' : '' }} {{ $storySection['donate'] ? 'hv2-stories--donate' : '' }}" aria-labelledby="home-v2-stories-{{ $storySection['id'] }}">
                <div class="hv2-section-head">
                    <h2 id="home-v2-stories-{{ $storySection['id'] }}">{{ $storySection['title'] }}</h2>
                    <div class="hv2-section-head__actions">
                        <div class="hv2-rail-controls" aria-label="Прокрутка раздела {{ $storySection['title'] }}">
                            <button type="button" data-rail-prev aria-label="Назад">←</button>
                            <button type="button" data-rail-next aria-label="Вперёд">→</button>
                        </div>
                        <a href="{{ $storySection['url'] }}">Смотреть все <span aria-hidden="true">→</span></a>
                    </div>
                </div>
                <div class="hv2-rail hv2-rail--stories" data-rail>
                    @foreach($storySection['items'] as $story)
                        @php
                            $storyCard = (new \App\Http\Resources\Home\StoryCardResource($story))->resolve(request());
                            $storyMedia = $storyCard['media'];
                            $storyLocked = $storyCard['access']['paid'] && !$storyCard['access']['viewed'];
                        @endphp
                        <a href="#story-popup"
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

    @if($homeV2Battles->isNotEmpty())
        <section class="hv2-shell hv2-section hv2-battles hv2-panel" aria-labelledby="home-v2-battles">
            <div class="hv2-section-head">
                <div><span class="hv2-section-kicker">Выбери сторону</span><h2 id="home-v2-battles">Батлы</h2></div>
                <div class="hv2-section-head__actions">
                    <div class="hv2-rail-controls" aria-label="Прокрутка батлов"><button type="button" data-rail-prev aria-label="Назад">←</button><button type="button" data-rail-next aria-label="Вперёд">→</button></div>
                    <a href="{{ route('battles.catalog') }}">Все батлы <span aria-hidden="true">→</span></a>
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
                                            <video src="{{ $opponent['media']['url'] }}" poster="{{ $opponent['media']['poster'] ?? '' }}" muted playsinline preload="none" aria-label="Участник батла"></video>
                                        @else
                                            <img src="{{ $opponent['media']['url'] ?? '/uploads/placeholder-image.png' }}" width="360" height="640" loading="lazy" alt="Участник батла">
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
                            <strong>{{ $battleCard['reward_amount'] > 0 ? number_format($battleCard['reward_amount'], 0, ',', ' ') . ' DEELS' : 'Открытый батл' }}</strong>
                            <span>{{ number_format($battleCard['stats']['likes'], 0, ',', ' ') }} голосов</span>
                            @if($battleDeadline)<time datetime="{{ $battleDeadline->toDateString() }}">до {{ $battleDeadline->format('d.m') }}</time>@endif
                        </div>
                        <a class="hv2-card-action" href="{{ $battleCard['url'] }}">Голосовать</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($homeV2Directions->isNotEmpty())
        <section class="hv2-shell hv2-section hv2-directions hv2-panel" aria-labelledby="home-v2-directions">
            <div class="hv2-section-head"><h2 id="home-v2-directions">Популярные направления</h2></div>
            <div class="hv2-direction-grid">
                @foreach($homeV2Directions as $direction)
                    <a class="hv2-direction" href="{{ route('campaigns.category', $direction->slug) }}">
                        <span aria-hidden="true">{{ $homeV2DirectionIcons[$loop->index] ?? '✦' }}</span>
                        <strong>{{ $direction->category_name }}</strong>
                        <small>{{ number_format((int) ($direction->campaigns_count ?? 0), 0, ',', ' ') }} проектов</small>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $homeV2CampaignSections = [
            ['id' => 'latest', 'title' => 'Недавно пополненные копилки', 'items' => $homeV2LatestCampaigns, 'url' => route('browse_campaign')],
            ['id' => 'new', 'title' => 'Новые копилки', 'items' => $homeV2NewCampaigns, 'url' => route('browse_campaign') . '?type=new'],
        ];
    @endphp

    @foreach($homeV2CampaignSections as $campaignSection)
        @if($campaignSection['items']->isNotEmpty())
            <section class="hv2-shell hv2-section hv2-funds hv2-panel" aria-labelledby="home-v2-funds-{{ $campaignSection['id'] }}">
                <div class="hv2-section-head">
                    <h2 id="home-v2-funds-{{ $campaignSection['id'] }}">{{ $campaignSection['title'] }}</h2>
                    <div class="hv2-section-head__actions">
                        <div class="hv2-rail-controls" aria-label="Прокрутка копилок"><button type="button" data-rail-prev aria-label="Назад">←</button><button type="button" data-rail-next aria-label="Вперёд">→</button></div>
                        <a href="{{ $campaignSection['url'] }}">Посмотреть все <span aria-hidden="true">→</span></a>
                    </div>
                </div>
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
                                <span class="hv2-progress" aria-label="Собрано {{ round($progress) }} процентов"><i style="width: {{ min(100, max(2, $progress)) }}%"></i></span>
                                <span class="hv2-fund-card__row"><b>{{ round($progress) }}%</b><small>{{ number_format((float) $campaignCard['funding']['raised'], 0, ',', ' ') }} из {{ number_format((float) $campaignCard['funding']['goal'], 0, ',', ' ') }} ₽</small></span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    <section class="hv2-shell hv2-section hv2-why hv2-panel" aria-labelledby="home-v2-why">
        <div>
            <h2 id="home-v2-why">Почему именно <span>DEELS?</span></h2>
            <div class="hv2-why__grid">
                <article><b>01</b><h3>Просто начать</h3><p>Понятные шаги помогают быстро выбрать челлендж, снять ответ или создать свою идею.</p></article>
                <article><b>02</b><h3>Удобный кошелёк</h3><p>Пополняйте баланс, поддерживайте авторов и управляйте средствами в одном месте.</p></article>
                <article><b>03</b><h3>Безопасность</h3><p>Контент проходит модерацию, а платежи выполняются через защищённые сценарии.</p></article>
                <article><b>04</b><h3>Живое сообщество</h3><p>Участники голосуют, поддерживают друг друга и помогают сильным идеям расти.</p></article>
            </div>
        </div>
        <aside class="hv2-community">
            <div class="hv2-community__faces" aria-hidden="true">
                @foreach($homeV2TopStories->take(6) as $story)
                    @php($face = (new \App\Http\Resources\Home\StoryCardResource($story))->resolve(request()))
                    <span style="background-image:url('{{ $face['media']['poster'] ?? $face['media']['url'] ?? '/uploads/placeholder-image.png' }}')"></span>
                @endforeach
            </div>
            <strong>{{ number_format((int) ($usersCount ?? 0), 0, ',', ' ') }}</strong>
            <p>пользователей уже создают и поддерживают идеи</p>
            <a class="hv2-card-action" href="{{ route('register') }}">Присоединиться</a>
        </aside>
    </section>

    <section class="hv2-shell hv2-section hv2-benefits hv2-panel" aria-labelledby="home-v2-benefits">
        <h2 id="home-v2-benefits">Deels — платформа для творчества,<br>поддержки и исполнения целей</h2>
        <div class="hv2-benefits__grid">
            <article><span aria-hidden="true">♡</span><h3>Зарабатывайте на контенте</h3><p>Получайте поддержку аудитории через сторис, челленджи и батлы.</p></article>
            <article><span aria-hidden="true">✦</span><h3>Создавайте активности</h3><p>Запускайте челленджи и объединяйте участников вокруг общей идеи.</p></article>
            <article><span aria-hidden="true">◎</span><h3>Копилки для целей</h3><p>Показывайте прогресс и собирайте поддержку друзей и подписчиков.</p></article>
            <article><span aria-hidden="true">◇</span><h3>Донаты без лишнего</h3><p>Поддерживайте авторов и управляйте балансом в одном приложении.</p></article>
        </div>
        <div class="hv2-trust"><span>◇ Безопасные платежи</span><span>✓ Модерация</span><span>◉ Поддержка</span><span>▣ Понятные правила</span></div>
    </section>

    <section class="hv2-shell hv2-section hv2-cta hv2-panel" aria-labelledby="home-v2-cta">
        <div>
            <span class="hv2-section-kicker">Твой первый шаг</span>
            <h2 id="home-v2-cta">Начни уже<br>сегодня</h2>
            <ul><li>Выбери интересный челлендж</li><li>Поддержи друзей и авторов</li><li>Создай сторис или свою копилку</li></ul>
            <div class="hv2-actions"><a class="hv2-btn hv2-btn--primary" href="{{ route('challenges.catalog') }}">Начать сейчас</a><a class="hv2-btn" href="{{ route('start_campaign') }}">Создать копилку</a></div>
        </div>
        <div class="hv2-cta__art" aria-hidden="true"><img src="/dist/images/home-v2-hero.png" width="640" height="640" alt=""></div>
    </section>

    @include('stories.modal')
</main>
@endsection

@section('page-js')
    <script src="{{ ext_asset('/dist/js/home-v2.js') }}" defer></script>
@endsection
